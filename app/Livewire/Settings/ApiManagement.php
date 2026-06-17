<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\ApiClient;
use App\Models\DynamicApi;
use Illuminate\Support\Str;

class ApiManagement extends Component
{
    public $clients = [];
    public $newClientName = '';
    
    // Properties for token generation
    public $showTokenModal = false;
    public $newTokenPlaintext = '';
    public $newClientForToken = null;
    
    public $showGenerateModal = false;
    public $clientForTokenId = null;

    public $availableEndpoints = [];
    public $abilities = [];

    // Dynamic API Builder Properties
    public $dynamicApis = [];
    public $showBuilderModal = false;
    public $isEditBuilder = false;
    
    public $builderApiId;
    public $builderMethod = 'GET';
    public $builderEndpoint;
    public $builderSqlQuery;
    public $builderDescription;

    public function mount()
    {
        $this->loadEndpoints();
        $this->loadClients();
        $this->loadDynamicApis();
        $this->resetAbilities();
    }

    public function loadEndpoints()
    {
        $this->availableEndpoints = [];
        $routes = \Illuminate\Support\Facades\Route::getRoutes();

        foreach ($routes as $route) {
            // Kita ambil route yang depannya 'api/'
            if (str_starts_with($route->uri(), 'api/')) {
                $middlewares = $route->middleware();
                $ability = null;
                
                // Cari middleware 'ability:xxx'
                if (is_array($middlewares)) {
                    foreach ($middlewares as $mw) {
                        if (is_string($mw) && str_starts_with($mw, 'ability:')) {
                            $ability = str_replace('ability:', '', $mw);
                            break;
                        }
                    }
                }

                // Jika butuh ability, kita daftarkan ke UI
                if ($ability) {
                    // Coba buat label yang cantik (misal: read:toko_pareto -> Read Toko Pareto)
                    $label = ucwords(str_replace(['_', '-', ':'], ' ', $ability));
                    
                    $this->availableEndpoints[] = [
                        'ability' => $ability,
                        'label' => $label,
                        'method' => implode('|', array_diff($route->methods(), ['HEAD'])),
                        'url' => '/' . $route->uri(),
                        'description' => 'Automated endpoint: ' . $route->uri(),
                    ];
                }
            }
        }
    }

    public $uniqueAbilities = [];

    public function resetAbilities()
    {
        $this->abilities = [];
        $this->uniqueAbilities = [];
        foreach ($this->availableEndpoints as $endpoint) {
            $this->abilities[$endpoint['ability']] = false;
            $this->uniqueAbilities[$endpoint['ability']] = $endpoint['label'];
        }
    }

    public function loadClients()
    {
        $this->clients = ApiClient::with('tokens')->orderBy('name')->get();
    }

    public function createClient()
    {
        $this->validate([
            'newClientName' => 'required|string|max:255|unique:api_clients,name',
        ]);

        ApiClient::create([
            'name' => $this->newClientName,
            'is_active' => true,
        ]);

        $this->newClientName = '';
        $this->loadClients();
        session()->flash('success', 'API Client created successfully.');
    }

    public function deleteClient($id)
    {
        $client = ApiClient::findOrFail($id);
        $client->tokens()->delete(); // Delete all tokens
        $client->delete();
        $this->loadClients();
        session()->flash('success', 'API Client and its tokens deleted successfully.');
    }

    public function openGenerateModal($clientId, $clientName)
    {
        $this->clientForTokenId = $clientId;
        $this->newClientForToken = $clientName;
        // reset abilities
        foreach ($this->abilities as $key => $val) {
            $this->abilities[$key] = false;
        }
        $this->showGenerateModal = true;
    }

    public function closeGenerateModal()
    {
        $this->showGenerateModal = false;
        $this->clientForTokenId = null;
        $this->newClientForToken = null;
    }

    public function generateToken()
    {
        $clientId = $this->clientForTokenId;
        $client = ApiClient::findOrFail($clientId);
        
        $selectedAbilities = [];
        foreach ($this->abilities as $ability => $isSelected) {
            if ($isSelected) {
                $selectedAbilities[] = $ability;
            }
        }

        $token = $client->createToken('token_'.now()->format('YmdHis'), $selectedAbilities);
        
        // Simpan token asli agar bisa dilihat terus di UI (Bypass hash Sanctum)
        \Illuminate\Support\Facades\DB::table('personal_access_tokens')
            ->where('id', $token->accessToken->id)
            ->update(['plain_text_token' => $token->plainTextToken]);
        
        $this->newTokenPlaintext = $token->plainTextToken;
        $this->showTokenModal = true;
        
        // Close the generate modal
        $this->closeGenerateModal();

        $this->abilities = []; // reset form
        $this->loadClients();
    }

    public function closeTokenModal()
    {
        $this->showTokenModal = false;
        $this->newTokenPlaintext = '';
        $this->newClientForToken = null;
    }

    public function revokeToken($clientId, $tokenId)
    {
        $client = ApiClient::findOrFail($clientId);
        $client->tokens()->where('id', $tokenId)->delete();
        $this->loadClients();
        session()->flash('success', 'Token revoked successfully.');
    }

    // ==========================================
    // DYNAMIC API BUILDER LOGIC
    // ==========================================

    public function loadDynamicApis()
    {
        $this->dynamicApis = DynamicApi::latest()->get();
    }

    public function openBuilderModal()
    {
        $this->resetValidation();
        $this->resetBuilderForm();
        $this->showBuilderModal = true;
    }

    public function closeBuilderModal()
    {
        $this->showBuilderModal = false;
    }

    public function editDynamicApi($id)
    {
        $this->resetValidation();
        $api = DynamicApi::findOrFail($id);
        $this->builderApiId = $api->id;
        $this->builderMethod = $api->method ?? 'GET';
        $this->builderEndpoint = $api->endpoint;
        $this->builderSqlQuery = $api->sql_query;
        $this->builderDescription = $api->description;
        $this->isEditBuilder = true;
        $this->showBuilderModal = true;
    }

    public function deleteDynamicApi($id)
    {
        DynamicApi::findOrFail($id)->delete();
        $this->loadDynamicApis();
        $this->loadEndpoints();
        $this->resetAbilities();
        session()->flash('success', 'API berhasil dihapus.');
    }

    public function saveDynamicApi()
    {
        $uniqueRule = $this->builderApiId ? 'unique:dynamic_apis,endpoint,' . $this->builderApiId : 'unique:dynamic_apis,endpoint';
        $this->validate([
            'builderMethod' => 'required|in:GET,POST,PUT,DELETE',
            'builderEndpoint' => ['required', 'string', 'regex:/^[a-zA-Z0-9\-]+$/', $uniqueRule],
            'builderSqlQuery' => 'required|string',
            'builderDescription' => 'nullable|string',
        ]);

        // Security check: If GET, must start with SELECT
        $queryUpper = strtoupper(trim($this->builderSqlQuery));
        if ($this->builderMethod === 'GET' && !str_starts_with($queryUpper, 'SELECT')) {
            $this->addError('builderSqlQuery', 'Security Error: Query GET MUST start dengan SELECT statement.');
            return;
        }

        if ($this->isEditBuilder) {
            $api = DynamicApi::findOrFail($this->builderApiId);
            $api->update([
                'method' => $this->builderMethod,
                'endpoint' => $this->builderEndpoint,
                'sql_query' => $this->builderSqlQuery,
                'description' => $this->builderDescription,
            ]);
            session()->flash('success', 'API berhasil diupdate.');
        } else {
            DynamicApi::create([
                'method' => $this->builderMethod,
                'endpoint' => $this->builderEndpoint,
                'sql_query' => $this->builderSqlQuery,
                'description' => $this->builderDescription,
            ]);
            session()->flash('success', 'API baru berhasil dibuat.');
        }

        $this->closeBuilderModal();
        $this->loadDynamicApis();
        $this->loadEndpoints();
        $this->resetAbilities();
    }

    public function resetBuilderForm()
    {
        $this->builderApiId = null;
        $this->builderMethod = 'GET';
        $this->builderEndpoint = '';
        $this->builderSqlQuery = '';
        $this->builderDescription = '';
        $this->isEditBuilder = false;
    }

    public function render()
    {
        $totalClients = ApiClient::count();
        $totalTokens = \Laravel\Sanctum\PersonalAccessToken::where('tokenable_type', ApiClient::class)->count();
        $totalEndpoints = count($this->availableEndpoints);

        return view('livewire.settings.api-management', [
            'totalClients' => $totalClients,
            'totalTokens' => $totalTokens,
            'totalEndpoints' => $totalEndpoints,
        ])->layout('layouts.app', ['title' => 'API Management']);
    }
}
