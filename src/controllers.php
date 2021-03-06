<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function () use ($app) {
    $one = mt_rand(1, 9);
    $two = mt_rand(1, 9);
    $captcha = $one + $two;

    $app['session']->set('captcha', $captcha);

    return $app['twig']->render('pages/index.html.twig', array(
            'one' => $one,
            'two' => $two
        )
    );
})->bind('inicio');

$app->post('/sendContact', function (Request $request) use ($app) {
    $json = [
        "status" => false
    ];
    if ($request->getMethod() == 'POST') {
        if ($app['session']->get('captcha') == $request->request->get('captcha')) {
            $body = $app['twig']->render('email.twig.html', array(
                    'nombre' => $request->request->get('name'),
                    'email' => $request->request->get('email'),
                    'mensaje' => $request->request->get('message')
                )
            );

            $transport = (new Swift_SmtpTransport('webmail.acceda.com.mx', 26))
                ->setUsername('no-reply@acceda.mx')
                ->setPassword('4eNo_[8.gRA6');

            $mailer = new Swift_Mailer($transport);

            $message = (new Swift_Message('Wonderful Subject'))
                ->setSubject('Contacto acceda.mx')
                ->setFrom(['no-reply@acceda.mx' => 'Contacto acceda'])
                ->setBcc(['rodrigo@acceda.mx' => 'Rodrigo Ramírez'])
                ->setTo([$request->request->get('email') => $request->request->get('name')])
                ->setBody($body, 'text/html');

            // Send message
            $result = $mailer->send($message);

            $json["status"] = true;
        } else {
            $json['message'] = "El resultado es incorrecto, intentalo de nuevo por favor.";
        }
    }

    return $app->json($json);
})->bind('contacto');

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
