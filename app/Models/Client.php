<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    public const COMPLIANCE_PERIODS = [
        'Mar 26', 'Apr 26', 'May 26', 'Jun 26', 'Jul 26',
        'Aug 26', 'Sep 26', 'Oct 26', 'Nov 26', 'Dec 26'
    ];

    protected $fillable = [
        'client_code',
        'name',
        'email',
        'phone',
        'tax_id',
        'notes',
        'migration_date',
        'client_pic',
        'location',
        'tax_status',
        'pph_scheme',
        'business_type',
        'contract_status',
        'start_date',
        'contract_duration_months',
        'end_contract_due_date',
        'client_type',
        'finance_package',
        'tax_package',
        'addon',
        'package_detail',
        'status',
        'files',
        'review_approval',
        'tax_pic',
        'accounting_pic',
    ];

    protected function casts(): array
    {
        return [
            'migration_date' => 'date',
            'start_date' => 'date',
            'end_contract_due_date' => 'date',
            'contract_duration_months' => 'integer',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function compliances(): HasMany
    {
        return $this->hasMany(ClientCompliance::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ClientDocument::class);
    }

    /**
     * Get or instantiate compliance records for all defined periods.
     */
    public function getCompliancesMap(): array
    {
        $existing = $this->compliances->keyBy('period');
        $map = [];

        foreach (self::COMPLIANCE_PERIODS as $period) {
            $map[$period] = $existing->get($period) ?? new ClientCompliance([
                'client_id' => $this->id,
                'period' => $period,
            ]);
        }

        return $map;
    }
}
