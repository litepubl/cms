<?php

namespace litepubl\widget;
use litepubl\core\Context;
    use litepubl\core\Response;
use litepubl\view\Theme;
use litepubl\view\Schema;

class Ajax implements \litepubl\core\ResponsiveInterface
{
public $url = '/getwidget.htm';

public function request(Context $context)
{
    $response = $context->response;
        $response->cache = false;
        $id = (int) $context->request->getArg('id');
        $sidebar = (int) $context->request->getArg('sidebar');
        $this->idUrlContext = (int) $context->request->getArg('idurl');

$widgets = Widgets::i();
        if (!$id || !$widgets->itemExists($id)) {
 return $this->errorRequest('Invalid params');
}

        $themename = $context->request->getArg('themename', Schema::i(1)->themename);
        if (!preg_match('/^\w[\w\.\-_]*+$/', $themename) || !Theme::exists($themename)) {
$themename = Schema::i(1)->themename;
}

        $theme = Theme::getTheme($themename);

        try {
            $response->body= $widgets->getWidgetContent($id, $sidebar);
        }
        catch(\Exception $e) {
            return $this->errorRequest('Cant get widget content');
        }
}

    private function errorRequest(Response $response, $mesg) {
$response->status = 400;
$response->body = $mesg;
    }

}