# Modulo para Magento 1.x - IoPay

## Documentação

Esse módulo para Magento 1.x permite integrar sua loja com a IoPay API.

Métodos de pagamento disponíveis:

- Pix
- Boleto
- Cartão de Crédito

## Requerimentos para integração
- [PHP 5.6+](https://www.php.net)
- [Magento 1.x](https://magento.com/tech-resources/download)
- [OpenMage](https://www.openmage.org/)

## Compatibilidades com OneStepCheckouts
- Inovarti Onestep Checkout
- Firecheckout
- Moip Checkout
- Onepage

## Instalação via GIT
    $ git clone https://github.com/iopay-payments/magento1 ~/iopay
    $ cp -r ~/iopay/* /dir/magento1x/

### Manual

1. Clique [aqui](https://github.com/iopay-payments/magento1) e baixe o arquivo `.zip` de nossa versão mais recente. O arquivo será semelhante a `iopay-payments-magento1-main.zip`
2. Descompacte o arquivo **zip** e copie as pastas `app` e `skin` na pasta raiz da sua instalação do Magento
3. Limpe o cache em `Sistema > Gerenciamento de Cache`

## Configuração

1. Acesse o painel administrativo da sua loja magento 1.x
2. Vá em `Sistema > Configuração > Métodos de Pagamento > IoPay - Preferências`
3. Escolha o **Ambiente** de atuação (Sandbox ou Production)
4. Em **Campo CPF** selecione de qual campo será enviado o CPF do usuário (Caso o seu checkout não possua o campo CPF nativo, selecione a opção IoPay Taxvat), consulte seu desenvolvedor
5. Em **Gerar Invoice** informe se você deseja criar uma fatura automaticamente quando o pedido for aprovado pela IoPay
6. Informe os dados para **IOPay E-mail**, **IOPay Secret** e seu **IOPay Seller Id**
7. Salve as configurações

Para que o magento receba as atualizações dos status dos pedidos pela API da IoPay, é necessário configurar a url de webhook na sua conta IoPay com a seguinte rota:

## Webhook Url
	$ https://domain.com.br/iopay/webhook

## API
https://docs-api.iopay.dev/