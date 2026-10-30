<?php

namespace relyzag\pulsereporter\controllers;

use Composer\InstalledVersions;
use Craft;
use craft\helpers\App;
use craft\web\Controller;
use relyzag\pulsereporter\Report;
use yii\web\Response;

class ReportController extends Controller
{
    protected array|int|bool $allowAnonymous = ['index'];

    public $enableCsrfValidation = false;

    public function actionIndex(): Response
    {
        $token = App::env('PULSE_TOKEN');
        [$status, $body] = Report::respond(
            $this->authorization(),
            is_string($token) ? $token : null,
            fn() => Report::build(Craft::$app->getVersion(), Craft::$app->edition->handle(), $this->packages()),
        );

        $response = $this->response;
        $response->setStatusCode($status);
        $response->getHeaders()->set('Cache-Control', 'no-store');
        if ($body === null) {
            $response->format = Response::FORMAT_RAW;
            $response->content = '';
        } else {
            $response->format = Response::FORMAT_JSON;
            $response->data = $body;
        }
        return $response;
    }

    private function authorization(): ?string
    {
        // Apache + PHP-FPM may only pass the header as REDIRECT_HTTP_AUTHORIZATION.
        return $this->request->getHeaders()->get('Authorization')
            ?? $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? null;
    }

    private function packages(): array
    {
        $composer = json_decode((string)file_get_contents(Craft::getAlias('@root/composer.json')), true);
        return Report::packages(
            $composer['require'] ?? [],
            fn(string $name) => InstalledVersions::isInstalled($name) ? InstalledVersions::getPrettyVersion($name) : null,
        );
    }
}
