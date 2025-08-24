<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FakeStore Service API - Documentação</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="\fakestore-service\resources\css\welcome.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-blue-600 mb-4">
                <i class="fas fa-store mr-3"></i>FakeStore Service API
            </h1>
            <p class="text-xl text-gray-600">Microserviço Laravel para integração com FakeStore API</p>
        </div>

        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-blue-500 text-2xl mb-4">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3">🚀 Comece Rápido</h3>
                <div class="space-y-2">
                    <div class="bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                        git clone [seu-repositorio]<br>
                        cd fakestore-service<br>
                        docker-compose up -d
                    </div>
                </div>
            </div>

            <!-- API Card -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-green-500 text-2xl mb-4">
                    <i class="fas fa-code"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3">🔌 Endpoints API</h3>
                <ul class="space-y-2">
                    <li><code class="bg-gray-100 p-1 rounded">GET /api/products</code></li>
                    <li><code class="bg-gray-100 p-1 rounded">GET /api/statistics/dashboard</code></li>
                    <li><code class="bg-gray-100 p-1 rounded">POST /api/integracoes/fakestore/sync</code></li>
                </ul>
            </div>

            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-purple-500 text-2xl mb-4">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3 class="text-xl font-semibold mb-3">⚙️ Requisitos</h3>
                <ul class="space-y-1">
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Docker & Docker Compose</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Git</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>4GB RAM mínimo</li>
                </ul>
            </div>
        </div>

        
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">
                <i class="fas fa-book-open mr-2"></i>Instruções Detalhadas
            </h2>

            <div class="space-y-6">
                
                <div>
                    <h3 class="text-lg font-semibold mb-2 text-blue-600">1. Clone o Repositório</h3>
                    <div class="bg-gray-800 text-green-400 p-4 rounded font-mono text-sm">
                        git clone https://github.com/seu-usuario/fakestore-service.git<br>
                        cd fakestore-service
                    </div>
                </div>

                
                <div>
                    <h3 class="text-lg font-semibold mb-2 text-blue-600">2. Configure o Ambiente</h3>
                    <div class="bg-gray-800 text-green-400 p-4 rounded font-mono text-sm">
                        cp .env.example .env<br>
                        # Configure as variáveis se necessário
                    </div>
                </div>

                
                <div>
                    <h3 class="text-lg font-semibold mb-2 text-blue-600">3. Inicie os Containers</h3>
                    <div class="bg-gray-800 text-green-400 p-4 rounded font-mono text-sm">
                        docker-compose up -d --build<br>
                        # Aguarde 1-2 minutos para inicialização
                    </div>
                </div>

                
                <div>
                    <h3 class="text-lg font-semibold mb-2 text-blue-600">4. Execute as Migrations</h3>
                    <div class="bg-gray-800 text-green-400 p-4 rounded font-mono text-sm">
                        docker-compose exec app php artisan migrate
                    </div>
                </div>

               
                <div>
                    <h3 class="text-lg font-semibold mb-2 text-blue-600">5. Sincronize os Produtos</h3>
                    <div class="bg-gray-800 text-green-400 p-4 rounded font-mono text-sm">
                        docker-compose exec app php artisan fakestore:sync
                    </div>
                </div>
            </div>
        </div>

       
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">
                <i class="fas fa-terminal mr-2"></i>Exemplos de Uso da API
            </h2>

            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Listar Produtos</h3>
                    <div class="bg-gray-800 text-green-400 p-4 rounded font-mono text-sm">
                        curl -H "X-Client-Id: test-client" \<br>
                        &nbsp;&nbsp;"http://localhost:8000/api/products?per_page=5"
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Buscar Estatísticas</h3>
                    <div class="bg-gray-800 text-green-400 p-4 rounded font-mono text-sm">
                        curl -H "X-Client-Id: test-client" \<br>
                        &nbsp;&nbsp;"http://localhost:8000/api/statistics/dashboard"
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-2">Sincronizar Produtos</h3>
                    <div class="bg-gray-800 text-green-400 p-4 rounded font-mono text-sm">
                        curl -X POST \<br>
                        &nbsp;&nbsp;-H "X-Client-Id: test-client" \<br>
                        &nbsp;&nbsp;-H "Content-Type: application/json" \<br>
                        &nbsp;&nbsp;"http://localhost:8000/api/integracoes/fakestore/sync"
                    </div>
                </div>
            </div>
        </div>

        
        <div class="text-center text-gray-600">
            <p>📚 Para mais detalhes, consulte o <a href="#" class="text-blue-600 hover:underline">README completo</a></p>
            <p class="mt-2">🐛 Problemas? <a href="#" class="text-blue-600 hover:underline">Abra uma issue</a></p>
        </div>
    </div>

    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
            const codeBlocks = document.querySelectorAll('.bg-gray-800');
            codeBlocks.forEach(block => {
                block.addEventListener('click', function() {
                    const text = this.textContent.replace(/\s+/g, ' ').trim();
                    navigator.clipboard.writeText(text).then(() => {
                        const original = this.textContent;
                        this.textContent = '✅ Copiado!';
                        setTimeout(() => {
                            this.textContent = original;
                        }, 2000);
                    });
                });
                block.style.cursor = 'pointer';
                block.title = 'Clique para copiar';
            });
        });
    </script>
</body>
</html>