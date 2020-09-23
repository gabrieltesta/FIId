<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Telegram\Bot\Api;
use Illuminate\Support\Facades\Log;
use Weidner\Goutte\GoutteFacade;
use Illuminate\Support\Facades\Cache;

class BotController extends Controller
{
    private $fiis;
    private $chat_id;
    private $token;
    private $bot;

    function __construct()
    {
        $this->token    = config('telegram.token');
        $this->chat_id  = config('telegram.chat_id');
        $this->fiis     = json_decode(\Storage::get('fii.json'));

        if(isset($this->token, $this->chat_id, $this->fiis))
            $this->bot = new Api($this->token);

        $this->validateBot();
    }

    public function __invoke()
    {
        $this->startBot();
    }

    private function startBot()
    {
        foreach($this->fiis as $fii) {
            $url = "https://www.fundsexplorer.com.br/funds/".$fii->ticker;
            if(!$cache = json_decode(Cache::get($fii->ticker)))
		$cache = [];
			 

	    if($comunicados = $this->getComunicados($url, $cache)){
                foreach($comunicados as $comunicado) {
                    if($this->enviaMensagemBot($fii->ticker, $comunicado)) {
			\Log::info($fii->ticker.'] Comunicado enviado: '$comunicado['nome'].$comunicado['url']);
			$cache[] = $comunicado['url'];
                    }
                }
		
		Cache::put($fii->ticker, json_encode($cache));
            }
        }

        die();
    }

    private function getComunicados($url, $cache)
    {
        $comunicados = [];
        $today      = date('d/m/Y');
        $thisMonth  = date('m/Y');

        try {
            $goutte = GoutteFacade::request('GET', $url);
            $goutte->filter('.bulletin-text-link')->each(function ($node) use (&$comunicados, $cache, $today, $thisMonth) {
                // Verifica se já foi enviado
                if(!in_array($node->attr('href'), $cache)) {
                    // Verifica se é do dia/mês corrente
                    if(strpos($node->text(), $today) !== false || strpos($node->text(), ' '.$thisMonth) !== false) {
                        $comunicados[] = [
                            'nome'  => $node->text(),
                            'url'   => $node->attr('href')
                        ];
                    }
                }
            });
        
	} catch(\Exception $e) {
            \Log::error('Erro no webscraper: '.$e->getMessage());
            return false;
        }

        return array_reverse($comunicados);
    }

    private function enviaMensagemBot($ticker, $mensagem) {
        try
        {
            $this->bot
                ->sendMessage([
                    'chat_id'                   => $this->chat_id,
                    'text'                      => "<b>$ticker</b>\r\n".$mensagem['nome']."\r\n<a href=\"".$mensagem['url']."\">Clique aqui para visualizar.</a>",
                    'parse_mode'                => 'html',
                    'disable_web_page_preview'  => true,
                    'disable_notification'      => true
                ]);
        }
        catch(\Exception $e)
        {
	    \Log::error($e->getMessage());
            \Log::error('['.$ticker.']Erro ao enviar a mensagem');
            return false;
        }

        \Log::info('['.$ticker.']Mensagem enviada com sucesso.');
        return true;
    }

    private function validateBot()
    {
        if(!$this->bot)
        {
            \Log::error('Erro ao criar instância do Bot.');
            die();
        }
    }
}
