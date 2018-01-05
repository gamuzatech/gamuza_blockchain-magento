<h1>Módulo de Pagamento com Criptomoeda Bitcoin para Carteira Blockchain.info</h1>

**Compatível com a plataforma Magento CE versão 1.6 a 1.9**

Agora os seu clientes também podem pagar suas compras usando o método de pagamento mais seguro na internet.

Com o nosso módulo de Bitcoin para a carteira Blockchain.info, você dispôe em sua loja virtual, uma das formas de pagamentos mais confiáveis da internet.

Você ainda pode gerenciar as suas transaçôes e consultar os pagamentos se necessário.

É possível configurar uma rotina que atualizará os preços das criptomoedas automaticamente ou ajustar de forma manual também.

E se desejar, ainda pode exibir os valores das criptomoedas na loja para o cliente em tempo real.

<img src="https://dl.dropboxusercontent.com/s/hxtc2sfo7nlodre/gamuza-blockchain-info-box.png" alt="" title="Gamuza Blockchain - Magento - Box" />

<h2>Instalação</h2>

<img src="https://dl.dropboxusercontent.com/s/pqpp0x62kqov683/sempre-faca-backup.png" alt="" title="Atenção! Sempre faça um backup da sua loja antes de realizar qualquer modificação!" />

**Instalar usando o modgit:**

    $ cd /path/to/magento
    $ modgit init
    $ modgit add gamuza_itaushopline https://github.com/gamuzatech/gamuza_blockchain-magento.git

**Instalação manual dos arquivos**

Baixe a ultima versão aqui do pacote Gamuza_Blockchain-xxx.tbz2 e descompacte o arquivo baixado para dentro do diretório principal do Magento

<img src="https://dl.dropboxusercontent.com/s/ir2vm6cyo3gl1v8/pos-instalacao.png" alt="Após a instalação, limpe os caches, rode a compilação, faça logout e login." title="Após a instalação, limpe os caches, rode a compilação, faça logout e login." />

<h2>Antes de Começar</h2>

**Solicitando uma chave de API para obter acesso às APIs Blockchain.info**

Antes de utilizar o módulo, é necessário solicitar uma chave da API em https://api.blockchain.info/v2/apikey/request/.

**Solicitando uma chave pública estendida (xPub)**

A API da Blockchain.info exige que você tenha uma conta BIP 32 xPub para receber pagamentos. A maneira mais fácil de começar a receber pagamentos é abrir uma Carteira Blockchain em https://blockchain.info/wallet/#/signup. Você deve criar uma nova conta dentro de sua carteira exclusivamente para transações. Para o módulo efetuar as chamadas de API, use o xPub para esta conta (localizado em Configurações -> Endereços -> Gerenciar -> Mais Opções -> Mostrar xPub).

<h2>Conhecendo o módulo</h2>

**1 - Habilitando a criptmoeda Bitcoin**

Nesta tela é possível habilitar as criptomoedas no sistema, configurar a rotina que atualizará o valor do câmbio automaticamente em um horário específico, e permitir ou não a exibição de valores em Bitcoin na loja.

<img src="https://dl.dropboxusercontent.com/s/arqnbkws2witwy3/gamuza-blockchain-info-admin-currency-config.png" alt="" title="Gamuza Blockchain - Magento - Configuração da Moeda no Painel Administrativo" />

**2 - Obtendo os valor de câmbio do bitcoin**

Aqui nesta tela podemos pegar o valor do câmbio do Bitcoin a qualquer momento e salvar para todas as lojas

<img src="https://dl.dropboxusercontent.com/s/teekeb7nvvtwxdm/gamuza-blockchain-info-admin-currency-rate.png" alt="" title="Gamuza Blockchain - Magento - Obtendo os valores de Câmbio no Painel Administrativo" />

**3 - Preenchendo as informaçes de pagamento**

<img src="https://dl.dropboxusercontent.com/s/6niy5gl6cqkl6sn/gamuza-blockchain-info-admin-payment-config.png" alt="" title="Gamuza Blockchain - Magento - Preenchendo as inforamçes de pagamento no Painel Administrativo" />

**4 - Consultando transações e confirmações de pagamento**

<img src="https://dl.dropboxusercontent.com/s/n80tmv81v07w3pm/gamuza-blockchain-info-admin-transactions.png" alt="" title="Gamuza Blockchain - Magento - Consultando transações e confirmaçes de pagamento" />

**5 - Consultando blocos e confirmações de pagamento**

<img src="https://dl.dropboxusercontent.com/s/qpevj02ik1230mg/gamuza-blockchain-info-admin-blocks.png" alt="" title="Gamuza Blockchain - Magento - Consultando blocos e confirmações de pagamento" />

**6 - Criando um pedido via painel administrativo**

<img src="https://dl.dropboxusercontent.com/s/mkue0em8no1g4jd/gamuza-blockchain-info-admin-order-checkout.png" alt="" title="Gamuza Blockchain - Magento - Criando um Pedido via Painel Administrativo" />

**7 - Consultando um pedido no painel administrativo**

<img src="https://dl.dropboxusercontent.com/s/8k743r61d1oquyj/gamuza-blockchain-info-admin-order-info.png" alt="" title="Gamuza Blockchain - Magento - Consultando um pedido no painel administrativo" />

**8 - Criando um pedido na loja**

<img src="https://dl.dropboxusercontent.com/s/c2fcxpyl5sibq73/gamuza-blockchain-info-checkout-payment-form.png" alt="" title="Gamuza Blockchain - Magento - Criando um Pedido na Loja" />

**9 - Criando um pedido na loja - informações**

<img src="https://dl.dropboxusercontent.com/s/1rvkn9pqo35n12g/gamuza-blockchain-info-checkout-payment-info.png" alt="" title="Gamuza Blockchain - Magento - Criando um Pedido na Loja - Informações" />

**10 - Página de sucesso do pedido**

<img src="https://dl.dropboxusercontent.com/s/c7e5jq8qf2mwco9/gamuza-blockchain-info-checkout-payment-success.png" alt="" title="Gamuza Blockchain - Magento - Página de Sucesso do Pedido" />

**11 - Pedido na tela do cliente**

<img src="https://dl.dropboxusercontent.com/s/x8t14dagvymydbg/gamuza-blockchain-info-sales-order-info.png" alt="" title="Gamuza Blockchain - Magento - Pedido na Tela do Cliente" />

**12 - Exibindo preços dos produtos em Bitcoin na loja**

É possível habilitar os preços em criptomoeda Bitcoin na loja. Basta habilitar em Sistema -> Configuração -> Configuração de Moedas -> Exibir Criptomoedas

<img src="https://dl.dropboxusercontent.com/s/oi0qeo9myl9yyhf/gamuza-blockchain-info-product-price-bitcoin.png" alt="" title="Gamuza Blockchain - Magento - Exibindo preços dos produtos em Bitcoin na loja" />
