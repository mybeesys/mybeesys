<?php

namespace App\Services;

use App\Jobs\SeedTenantDatabase;
use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Jobs\MigrateDatabase;


class CompanyAction
{

    public function __construct(protected User $user)
    {
    }

    public function storeCompany($data)
    {
        $tenant = null;
        $company = null;
        try {
            $company = Company::create([
                'name' => $data['companyName'],
                'phone' => $data['companyPhone'],
                'website' => $data['website'],
                'ceo_name' => $data['ceo_name'],
                'tax_name' => $data['tax_name'],
                'country' => $data['country_id'],
                'state' => $data['state'],
                'city' => $data['city'],
                'national_address' => $data['national_address'],
                'zip_code' => $data['zipcode'],
                'description' => $data['description'] ?? null,
                'logo' => $data['logo'] ?? null
            ]);

            $tenant = Tenant::create([
                'id' => trim($data['companyName']),
                'company_id' => $company->id,
                'user_id' => $this->user->id
            ]);


            Domain::create([
                'domain' => trim($data['companyName']) . '.' . str_replace(['http://', 'https://'], '', config('app.url')),
                'tenant_id' => trim($data['companyName'])
            ]);

            MigrateDatabase::withChain([
                new SeedTenantDatabase($tenant)
            ])->dispatch($tenant);

            return $company;
        } catch (\Throwable $e) {
            $this->cleanup($tenant, $company);
            throw $e;
        }
    }

    private function cleanup(?Tenant $tenant, ?Company $company)
    {
        if ($tenant) {
            try {
                $tenant->domains()->delete();
                $tenant->delete();
            } catch (\Exception $e) {
                \Log::error('Tenant cleanup error: ' . $e->getMessage());
            }
        }

        if ($company) {
            try {
                $company->forceDelete();
            } catch (\Exception $e) {
                \Log::error('Company cleanup error: ' . $e->getMessage());
            }
        }

        try {
            $this->user->forceDelete();
        } catch (\Exception $e) {
            \Log::error('User cleanup error: ' . $e->getMessage());
        }
    }

}