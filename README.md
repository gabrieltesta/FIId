
# FIId

**FIId** foi criado pensando-se na necessidade de um Feed de comunicados de Fundos de Investimento Imobiliários, os FIIs, dando assim seu nome Feed + FII = FIId.

É difícil acompanhar os comunicados, que nem sempre saem em dias programados, ou em horário comercial. Possuir um feed automático no Telegram facilita imensamente este processo de acompanhar os FIIs de sua carteira.

O Bot funciona com um webscraper, lendo a seção de Comunicados, de cada FII informado no arquivo fii.json(storage/app/fii.json), um a um, e enviando uma mensagem ao bot no telegram caso possua as seguinte condições:

 - Possui em seu nome a data atual (dd/mm/yyyy) ou espaço + mês/ano atual ( mm/yyyy);
 - Não está salvo no arquivo de cache físico.

Os comunicados enviados são salvos em arquivos de Cache físicos utilizando o próprio Laravel, de forma que não tenha envios repetidos, com um arquivo por FII. A utilização de cache também é utilizada para evitar o uso de um banco de dados, desnecessário para uma aplicação deste tamanho.

## Tecnologias utilizadas
 - PHP 7;
 - [Laravel 7](https://laravel.com/);
 - [Telegram Bot SDK (irazasyed/telegram-bot-sdk)](https://github.com/irazasyed/telegram-bot-sdk);
 - [Laravel Facade for Goutte (dweidner/laravel-goutte)](https://github.com/dweidner/laravel-goutte).
 
 O Bot é executado via CRON em uma AWS EC2 (t2.micro) a cada 30 minutos.

## Canais

 - [FIId](https://t.me/fiid_feed_fii) 
 
## Utilização

Apenas faça um git clone, e, no arquivo .env, insira os valores:

 - TELEGRAM_CHAT_ID => Com o chat ID que deseja que envie a mensagem;
 - TELEGRAM_TOKEN => Token do Telegram Bot.
