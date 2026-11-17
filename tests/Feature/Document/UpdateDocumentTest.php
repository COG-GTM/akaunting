<?php

namespace Tests\Feature\Document;

use App\Jobs\Banking\CreateBankingDocumentTransaction;
use App\Jobs\Document\CreateDocument;
use App\Jobs\Document\UpdateDocument;
use App\Models\Document\Document;
use App\Models\Document\DocumentItem;
use App\Models\Document\DocumentItemTax;
use App\Models\Document\DocumentTotal;
use Illuminate\Database\Eloquent\Collection;
use Tests\Feature\FeatureTestCase;

/**
 * Updating a document's lines replaces the stored ones, whatever the model
 * instance has loaded before.
 */
class UpdateDocumentTest extends FeatureTestCase
{
    public function testItReplacesTheStoredLinesWhenTheLoadedOnesAreStale(): void
    {
        $invoice = Document::factory()->invoice()->draft()->create();

        // The factory's own update read these before creating them; unless a listener refreshes the model, it keeps the empty lists
        foreach (['items', 'item_taxes', 'totals'] as $relation) {
            $invoice->setRelation($relation, new Collection());
        }

        $request = Document::factory()->invoice()->items()->raw();
        $request['contact_id'] = $invoice->contact_id;
        $request['items'][0]['name'] = 'Replacement';

        $this->dispatch(new UpdateDocument($invoice, $request));

        $this->assertSame(['Replacement'], DocumentItem::where('document_id', $invoice->id)->pluck('name')->all());
        $this->assertSame(1, DocumentItemTax::where('document_id', $invoice->id)->count());
        $this->assertSame(1, DocumentTotal::where('document_id', $invoice->id)->where('code', 'total')->count());
    }

    public function testItRejectsATotalBelowWhatWasAlreadyPaid(): void
    {
        $invoice = $this->createPartlyPaidInvoice(3, 19.69, 20.00);

        try {
            $this->dispatch(new UpdateDocument($invoice, $this->getUpdateRequest($invoice, 1, 19.69)));

            $this->fail('An update below the paid amount must be rejected.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('can not be less than the amount already paid', $e->getMessage());
            $this->assertStringContainsString('20.00', $e->getMessage());
        }

        $invoice = Document::find($invoice->id);

        $this->assertSame('partial', $invoice->status);
        $this->assertEquals(59.07, $invoice->amount);
        $this->assertEquals(20.00, $invoice->paid);
        $this->assertEquals(['3'], DocumentItem::where('document_id', $invoice->id)->pluck('quantity')->map(fn ($q) => (string) (int) $q)->all());
    }

    public function testItShowsTheRejectionWhenTheInvoiceFormLowersTheTotalBelowWhatWasPaid(): void
    {
        $invoice = $this->createPartlyPaidInvoice(3, 19.69, 20.00);

        $this->loginAs()
            ->patch(route('invoices.update', $invoice->id), $this->getUpdateRequest($invoice, 1, 19.69))
            ->assertStatus(200)
            ->assertJson(['success' => false]);

        $this->assertFlashLevel('danger');

        $this->assertDatabaseHas('documents', [
            'id' => $invoice->id,
            'status' => 'partial',
            'amount' => 59.07,
        ]);
    }

    public function testItReturnsAValidationErrorWhenTheApiLowersTheTotalBelowWhatWasPaid(): void
    {
        $invoice = $this->createPartlyPaidInvoice(3, 19.69, 20.00);

        $this->withExceptionHandling()
            ->withHeaders([
                'Authorization' => 'Basic ' . base64_encode('test@company.com:123456'),
            ])
            ->patchJson(route('api.documents.update', ['document' => $invoice->id, 'search' => 'type:invoice']), $this->getUpdateRequest($invoice, 1, 19.69))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        $this->assertDatabaseHas('documents', [
            'id' => $invoice->id,
            'status' => 'partial',
            'amount' => 59.07,
        ]);
    }

    public function testItMarksTheDocumentPaidWhenTheTotalIsLoweredToWhatWasPaid(): void
    {
        $invoice = $this->createPartlyPaidInvoice(3, 19.69, 20.00);

        $this->dispatch(new UpdateDocument($invoice, $this->getUpdateRequest($invoice, 1, 20.00)));

        $invoice = Document::find($invoice->id);

        $this->assertSame('paid', $invoice->status);
        $this->assertEquals(20.00, $invoice->amount);
    }

    public function testItKeepsTheDocumentPartialWhenTheTotalStaysAboveWhatWasPaid(): void
    {
        $invoice = $this->createPartlyPaidInvoice(3, 19.69, 20.00);

        $this->dispatch(new UpdateDocument($invoice, $this->getUpdateRequest($invoice, 2, 19.69)));

        $invoice = Document::find($invoice->id);

        $this->assertSame('partial', $invoice->status);
        $this->assertEquals(39.38, $invoice->amount);
        $this->assertEquals(20.00, $invoice->paid);
    }

    public function testItComparesTheTotalWithThePaymentsConvertedToTheEditedCurrency(): void
    {
        $invoice = $this->createPartlyPaidInvoice(1, 100, 50);

        // 50 USD paid is 25 EUR at 0.5, so a 25 EUR total is settled, not below what was paid
        $request = $this->getUpdateRequest($invoice, 1, 25);
        $request['currency_code'] = 'EUR';
        $request['currency_rate'] = 0.5;
        $request['items'][0]['currency'] = 'EUR';

        $this->dispatch(new UpdateDocument($invoice, $request));

        $invoice = Document::find($invoice->id);

        $this->assertSame('EUR', $invoice->currency_code);
        $this->assertEquals(25, $invoice->amount);
        $this->assertSame('paid', $invoice->status);
    }

    public function testItRejectsATotalBelowThePaymentsConvertedToTheEditedCurrency(): void
    {
        $invoice = $this->createPartlyPaidInvoice(1, 100, 50);

        $request = $this->getUpdateRequest($invoice, 1, 20);
        $request['currency_code'] = 'EUR';
        $request['currency_rate'] = 0.5;
        $request['items'][0]['currency'] = 'EUR';

        try {
            $this->dispatch(new UpdateDocument($invoice, $request));

            $this->fail('An update below the converted paid amount must be rejected.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('can not be less than the amount already paid', $e->getMessage());
            $this->assertStringContainsString('25,00', $e->getMessage());
        }

        $invoice = Document::find($invoice->id);

        $this->assertSame('USD', $invoice->currency_code);
        $this->assertEquals(100, $invoice->amount);
        $this->assertSame('partial', $invoice->status);
    }

    public function testItRejectsABillTotalBelowWhatWasAlreadyPaid(): void
    {
        $request = Document::factory()->bill()->items()->raw(['status' => 'received']);
        $request['items'][0]['tax_ids'] = [];
        $request['items'][0]['quantity'] = 2;
        $request['items'][0]['price'] = 50;

        $bill = $this->dispatch(new CreateDocument($request));

        $this->dispatch(new CreateBankingDocumentTransaction($bill, [
            'amount' => 100,
            'currency_code' => $bill->currency_code,
            'currency_rate' => $bill->currency_rate,
            'type' => 'expense',
        ]));

        $bill = Document::find($bill->id);
        $this->assertSame('paid', $bill->status);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/can not be less than the amount already paid/');

        $this->dispatch(new UpdateDocument($bill, $this->getUpdateRequest($bill, 1, 50)));
    }

    protected function createPartlyPaidInvoice(int $quantity, float $price, float $payment): Document
    {
        $request = Document::factory()->invoice()->items()->raw(['status' => 'sent']);
        $request['items'][0]['tax_ids'] = [];
        $request['items'][0]['quantity'] = $quantity;
        $request['items'][0]['price'] = $price;

        $invoice = $this->dispatch(new CreateDocument($request));

        $this->dispatch(new CreateBankingDocumentTransaction($invoice, [
            'amount' => $payment,
            'currency_code' => $invoice->currency_code,
            'currency_rate' => $invoice->currency_rate,
            'type' => 'income',
        ]));

        $invoice = Document::find($invoice->id);

        $this->assertSame('partial', $invoice->status);

        return $invoice;
    }

    protected function getUpdateRequest(Document $document, int $quantity, float $price): array
    {
        $item = $document->items()->first();

        $request = Document::factory()->{$document->type}()->items()->raw([
            'contact_id' => $document->contact_id,
            'document_number' => $document->document_number,
            'currency_code' => $document->currency_code,
            'currency_rate' => $document->currency_rate,
            'category_id' => $document->category_id,
            'status' => $document->status,
        ]);
        $request['items'] = [[
            'type' => $document->type,
            'name' => $item->name,
            'item_id' => $item->item_id,
            'tax_ids' => [],
            'quantity' => $quantity,
            'price' => $price,
            'currency' => $document->currency_code,
        ]];

        return $request;
    }
}
