<?php

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;

$app->match('/', function() use ($app) {
    return $app['twig']->render('index.html.twig');
})->bind('homepage');

$app->match('/menu', function() use ($app) {

    $spaces = array();
    $locales = array();

    $finder = new Finder();
    $finder->files()->in($app['translations.folder'])->name('*.yml');
    foreach ($finder as $file) {
        $explodedFilename = explode(".", $file->getFilename());
        if (!in_array($explodedFilename[0], $spaces)) {
            $spaces[] = $explodedFilename[0];
        }
        if (!in_array($explodedFilename[1], $locales)) {
            $locales[] = $explodedFilename[1];
        }
    }
    return $app['twig']->render('menu.html.twig', array(
                'spaces' => $spaces,
                'locales' => $locales,
                'activeSpace' => $app['session']->get('space'),
                'activeLocale' => $app['session']->get('locale')
    ));
});

$app->match('/edit/{file}', function($file) use ($app) {

    $filepath = $app['translations.folder'] . '/' . $file;
    if (!file_exists($filepath)) {
        throw new \Exception(sprintf("File %s not found", $filepath));
    }

    $explodedFilename = explode(".", $file);
    $app['session']->set('space', $explodedFilename[0]);
    $app['session']->set('locale', $explodedFilename[1]);

    $lines = Yaml::parse($filepath);
    return $app['twig']->render('edit.html.twig', array(
                'file' => $file,
                'lines' => $lines));
})->bind('edit');

$app->post('/save', function(Request $request) use ($app) {

    $file = $request->get('file');
    $content = $request->get('content');
    $key = $request->get('key');

    $filepath = $app['translations.folder'] . '/' . $file;
    if (!file_exists($filepath)) {
        throw new \Exception(sprintf("File %s not found", $filepath));
    }

    $lines = Yaml::parse($filepath);
    $lines[$key] = $content;

    if (file_put_contents($filepath, Yaml::dump($lines))) {
        return new Response();
    } else {
        return new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException();
    }
})->bind('save');

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message, $code);
});

return $app;
