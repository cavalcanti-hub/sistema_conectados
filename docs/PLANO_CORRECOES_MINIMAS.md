# Plano de correções mínimas comprovadas

## 1. Remover DDL de construtores

- Módulos: PDV/Point; gravidade: alta; prioridade: P0.
- Evidência: `PdvModel::__construct()` e `MercadoPagoPointModel` executam ensure/ALTER.
- Reprodução: instanciar o model com coluna/tabela ausente executa DDL.
- Impacto: locks e schema divergente em requisição.
- Correção mínima: retirar chamadas automáticas e exigir migration CLI versionada.
- Arquivos prováveis: models citados e `Database.php`.
- Risco: instalações incompletas deixam de se autocorrigir; testar restauração/migration em cópia de cada empresa.

## 2. Exigir assinatura no webhook

- Módulo: webhook Point; gravidade crítica; prioridade P0.
- Evidência: rota pública apenas declara `external_auth`; controller não valida headers/assinatura.
- Impacto: evento não autenticado pode alcançar processamento financeiro.
- Correção mínima: validar assinatura oficial antes de interpretar payload e falhar fechado; não continuar com payload quando consulta autenticada falhar.
- Arquivos: `Router.php`, `MercadoPagoController.php`, `MercadoPagoPointModel.php`.
- Risco/testes: rejeição indevida de eventos; fixtures assinadas, replay, timestamp e idempotência.

## 3. Reativar verificação TLS

- Módulo: Point; gravidade crítica; prioridade P0.
- Evidência: `CURLOPT_SSL_VERIFYPEER=false`.
- Correção mínima: remover override e configurar CA válida/timeouts.
- Arquivo: `MercadoPagoController.php`.
- Testes: mock TLS válido/inválido, timeout e ausência de token em logs.

## 4. Desabilitar exclusão física de OS

- Módulo: OS; gravidade alta; prioridade P0.
- Evidência: `OsModel::delete()` executa DELETE em cascata manual.
- Correção mínima: bloquear rota na versão congelada e, em etapa autorizada, substituir por cancelamento lógico auditado.
- Arquivos: rota, controller, model e views de OS.
- Testes: permissão, CSRF, histórico, financeiro e arquivos.

## 5. Unificar pagamento legado

- Módulo: OS/financeiro; gravidade alta; prioridade P0.
- Evidência: `registerOsPaymentFromPost()` usa float e inserts diretos.
- Correção mínima: encaminhar o fluxo legado ao serviço transacional já existente, mantendo PRG e nonce.
- Arquivos: `OsController.php`, `ManualOsPaymentService.php` e views.
- Testes: parcial/integral, acima do saldo, clique duplo e dois processos.

## 6. Proteger PIN e logs

- Módulos: aparelhos/logs; gravidade média-alta; prioridade P1.
- Evidência: PIN em texto puro e payloads completos em logs de integração.
- Correção mínima: plano separado de criptografia autenticada e redaction; sem alteração de schema nesta etapa.
- Testes: migração reversível em cópia, autorização de visualização e ausência em logs/impressos.
