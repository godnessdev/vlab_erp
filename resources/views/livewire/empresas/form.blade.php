<div>
    <flux:modal.header>
        <flux:heading size="lg">
            {{ $modoEdicao ? 'Editar Empresa' : 'Nova Empresa' }}
        </flux:heading>
    </flux:modal.header>

    <form wire:submit="salvar">
        <flux:modal.body class="space-y-6">
            {{-- Dados Básicos --}}
            <div class="space-y-4">
                <flux:heading size="sm">Dados Básicos</flux:heading>
                
                <flux:field>
                    <flux:label>Nome da Empresa *</flux:label>
                    <flux:input 
                        wire:model="nome" 
                        placeholder="Ex: Empresa de Serviços Ltda"
                        required
                    />
                    <flux:error name="nome" />
                </flux:field>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>CNPJ *</flux:label>
                        <flux:input 
                            wire:model="cnpj"
                            wire:blur="formatarCnpj"
                            placeholder="00.000.000/0000-00"
                            maxlength="18"
                            required
                        />
                        <flux:error name="cnpj" />
                        <flux:description>Formato: 00.000.000/0000-00</flux:description>
                    </flux:field>

                    <flux:field>
                        <flux:label>Regime Tributário *</flux:label>
                        <flux:select wire:model="regime_tributario" required>
                            @foreach($regimes as $regime)
                                <option value="{{ $regime['value'] }}">{{ $regime['label'] }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="regime_tributario" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <flux:field>
                        <flux:label>Inscrição Estadual</flux:label>
                        <flux:input 
                            wire:model="ie" 
                            placeholder="000.000.000.000"
                        />
                        <flux:error name="ie" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Inscrição Municipal</flux:label>
                        <flux:input 
                            wire:model="im" 
                            placeholder="000000"
                        />
                        <flux:error name="im" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Data de Constituição</flux:label>
                        <flux:input 
                            type="date" 
                            wire:model="data_constituicao"
                        />
                        <flux:error name="data_constituicao" />
                    </flux:field>
                </div>
            </div>

            {{-- Dados de Contato --}}
            <div class="space-y-4">
                <flux:heading size="sm">Dados de Contato</flux:heading>
                
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:field>
                        <flux:label>E-mail de Contato</flux:label>
                        <flux:input 
                            type="email"
                            wire:model="email_contato" 
                            placeholder="contato@empresa.com.br"
                        />
                        <flux:error name="email_contato" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Telefone de Contato</flux:label>
                        <flux:input 
                            wire:model="telefone_contato" 
                            placeholder="(00) 0000-0000"
                        />
                        <flux:error name="telefone_contato" />
                    </flux:field>
                </div>
            </div>

            @if(!$modoEdicao)
                <flux:note variant="info">
                    <strong>Atenção:</strong> Ao criar uma empresa, uma filial matriz será criada automaticamente.
                </flux:note>
            @endif
        </flux:modal.body>

        <flux:modal.footer>
            <div class="flex gap-2">
                <flux:button type="button" wire:click="cancelar" variant="ghost">
                    Cancelar
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $modoEdicao ? 'Atualizar' : 'Cadastrar' }}
                </flux:button>
            </div>
        </flux:modal.footer>
    </form>
</div>
