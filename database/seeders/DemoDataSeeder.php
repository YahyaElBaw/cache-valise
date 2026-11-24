<?php

namespace Database\Seeders;

use App\Enums\BatchStatus;
use App\Enums\CashMovementType;
use App\Enums\ChargeCategory;
use App\Enums\PaymentMethod;
use App\Models\Batch;
use App\Models\CashMovement;
use App\Models\Charge;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\ProductModel;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Support\ActivityLogger;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $cv001 = ProductModel::create([
            'reference' => 'CV001',
            'designation' => 'Cache-valise petit',
            'dimensions' => '55 x 40 x 20 cm (Cabine)',
            'description' => 'Housse élastique renforcée format cabine.',
            'suggested_price' => '35.000',
            'is_active' => true,
        ]);

        $cv002 = ProductModel::create([
            'reference' => 'CV002',
            'designation' => 'Cache-valise moyen',
            'dimensions' => '68 x 48 x 28 cm (Moyen)',
            'description' => 'Housse imprimée haute résistance format moyen.',
            'suggested_price' => '45.000',
            'is_active' => true,
        ]);

        $cv003 = ProductModel::create([
            'reference' => 'CV003',
            'designation' => 'Cache-valise grand',
            'dimensions' => '78 x 54 x 32 cm (Grand)',
            'description' => 'Housse grand format avec fermeture renforcée.',
            'suggested_price' => '55.000',
            'is_active' => true,
        ]);

        $supFabric = Supplier::create([
            'name' => 'Tissu El Medina',
            'phone' => '71 234 567',
            'address' => 'Rue des Tisserands, Tunis',
            'type' => ChargeCategory::FABRIC,
        ]);

        $supSewing = Supplier::create([
            'name' => 'Atelier Couture Ben Ali',
            'phone' => '22 456 789',
            'address' => 'Zone Industrielle Ben Arous',
            'type' => ChargeCategory::SEWING,
        ]);

        $supPrinting = Supplier::create([
            'name' => 'Imprimerie Carthage',
            'phone' => '55 987 654',
            'address' => 'Ariana Centre',
            'type' => ChargeCategory::PRINTING,
        ]);

        $supOther = Supplier::create([
            'name' => 'Transport Express TN',
            'phone' => '98 112 233',
            'address' => 'Tunis',
            'type' => ChargeCategory::OTHER,
        ]);

        $cust1 = Customer::create([
            'name' => 'Boutique Atlas Tunis',
            'phone' => '71 800 100',
            'address' => 'Avenue Habib Bourguiba, Tunis',
            'email' => 'contact@atlas-tunis.tn',
        ]);

        $cust2 = Customer::create([
            'name' => 'Voyages Sahel Sousse',
            'phone' => '73 400 200',
            'address' => 'Boulevard 14 Janvier, Sousse',
            'email' => 'achats@sahel-voyages.tn',
        ]);

        // Solde initial de caisse
        CashMovement::create([
            'moved_at' => now()->startOfMonth(),
            'type' => CashMovementType::OPENING,
            'direction' => 'in',
            'amount' => '3000.000',
            'label' => 'Solde initial de caisse',
        ]);

        // Lot de démonstration basé sur l'exemple du cahier des charges
        $batch1 = Batch::create([
            'number' => 'LOT-2026-D01',
            'product_model_id' => $cv002->id,
            'quantity' => 100,
            'production_date' => now()->subDays(10)->toDateString(),
            'start_date' => now()->subDays(12)->toDateString(),
            'end_date' => now()->subDays(10)->toDateString(),
            'status' => BatchStatus::DONE,
            'notes' => 'Lot de référence (100 unités moyennes)',
        ]);

        $c1 = Charge::create([
            'number' => 'CHG-2026-0001',
            'charge_date' => now()->subDays(12)->toDateString(),
            'batch_id' => $batch1->id,
            'supplier_id' => $supFabric->id,
            'category' => ChargeCategory::FABRIC,
            'label' => 'Achat tissu lycra renforcé',
            'quantity' => '100.000',
            'unit' => 'm',
            'unit_price' => '8.000',
            'status' => 'active',
        ]);

        $c2 = Charge::create([
            'number' => 'CHG-2026-0002',
            'charge_date' => now()->subDays(11)->toDateString(),
            'batch_id' => $batch1->id,
            'supplier_id' => $supSewing->id,
            'category' => ChargeCategory::SEWING,
            'label' => 'Façon couture et surjet',
            'quantity' => '100.000',
            'unit' => 'pcs',
            'unit_price' => '2.500',
            'status' => 'active',
        ]);

        $c3 = Charge::create([
            'number' => 'CHG-2026-0003',
            'charge_date' => now()->subDays(10)->toDateString(),
            'batch_id' => $batch1->id,
            'supplier_id' => $supPrinting->id,
            'category' => ChargeCategory::PRINTING,
            'label' => 'Impression sublimation motifs',
            'quantity' => '100.000',
            'unit' => 'pcs',
            'unit_price' => '1.500',
            'status' => 'active',
        ]);

        Charge::create([
            'number' => 'CHG-2026-0004',
            'charge_date' => now()->subDays(10)->toDateString(),
            'batch_id' => $batch1->id,
            'supplier_id' => $supOther->id,
            'category' => ChargeCategory::OTHER,
            'label' => 'Emballage et transport',
            'quantity' => '1.000',
            'unit' => 'forfait',
            'unit_price' => '50.000',
            'status' => 'active',
        ]);

        // Paiement partiel fournisseur en espèces (300 DT sur c1)
        $supPay = Payment::create([
            'payment_date' => now()->subDays(9)->toDateString(),
            'direction' => 'out',
            'charge_id' => $c1->id,
            'amount' => '300.000',
            'method' => PaymentMethod::CASH,
            'reference' => 'ESP-F01',
            'status' => 'valid',
        ]);

        CashMovement::create([
            'moved_at' => now()->subDays(9),
            'type' => CashMovementType::CHARGE_PAYMENT,
            'direction' => 'out',
            'amount' => '300.000',
            'label' => 'Paiement charge ' . $c1->number . ' (Tissu El Medina)',
            'payment_id' => $supPay->id,
        ]);

        // Deuxième lot en cours
        $batch2 = Batch::create([
            'number' => 'LOT-2026-D02',
            'product_model_id' => $cv001->id,
            'quantity' => 60,
            'production_date' => now()->subDays(3)->toDateString(),
            'start_date' => now()->subDays(4)->toDateString(),
            'status' => BatchStatus::PREPARING,
            'notes' => 'Lot petit format en cours',
        ]);

        Charge::create([
            'number' => 'CHG-2026-0005',
            'charge_date' => now()->subDays(4)->toDateString(),
            'batch_id' => $batch2->id,
            'supplier_id' => $supFabric->id,
            'category' => ChargeCategory::FABRIC,
            'label' => 'Tissu cabine Lot D02',
            'quantity' => '50.000',
            'unit' => 'm',
            'unit_price' => '7.500',
            'status' => 'active',
        ]);

        // Vente de démonstration: 40 unités à 45 DT = 1800 DT, coût unitaire snapshot = 12,500 DT
        $sale = Sale::create([
            'invoice_number' => 'FAC-2026-D001',
            'sale_date' => now()->subDays(5)->toDateString(),
            'customer_id' => $cust1->id,
            'subtotal' => '1800.000',
            'discount_total' => '0.000',
            'total' => '1800.000',
            'status' => 'validated',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'batch_id' => $batch1->id,
            'quantity' => 40,
            'unit_price' => '45.000',
            'discount' => '0.000',
            'unit_cost' => '12.500',
            'line_total' => '1800.000',
        ]);

        // Règlement client en espèces de 1000 DT (reste dû = 800 DT)
        $custPay = Payment::create([
            'payment_date' => now()->subDays(4)->toDateString(),
            'direction' => 'in',
            'sale_id' => $sale->id,
            'amount' => '1000.000',
            'method' => PaymentMethod::CASH,
            'reference' => 'ESP-C01',
            'status' => 'valid',
        ]);

        CashMovement::create([
            'moved_at' => now()->subDays(4),
            'type' => CashMovementType::CUSTOMER_PAYMENT,
            'direction' => 'in',
            'amount' => '1000.000',
            'label' => 'Règlement facture ' . $sale->invoice_number . ' (Boutique Atlas Tunis)',
            'payment_id' => $custPay->id,
        ]);

        ActivityLogger::log('seed', 'System', null, 'Initialisation des données de démonstration.');
    }
}
