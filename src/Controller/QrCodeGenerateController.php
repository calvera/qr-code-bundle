<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\QrCodeBundle\Controller;

use Endroid\QrCode\Exception\UnsupportedExtensionException;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QrCodeGenerateController
{
    private $qrCodeFactory;

    public function __construct(QrCodeFactoryInterface $qrCodeFactory)
    {
        $this->qrCodeFactory = $qrCodeFactory;
    }

    public function __invoke(Request $request, string $text, string $extension): Response
    {
        $options = $request->query->all();

        $qrCode = $this->qrCodeFactory->create($text, $options);

        if ($qrCode instanceof QrCode) {
            try {
                $qrCode->setWriterByExtension($extension);
            } catch (UnsupportedExtensionException $e) {
                throw new NotFoundHttpException("Extension '$extension' is not a supported extension.");
            }
        }

        return new Response($qrCode->writeString(), Response::HTTP_OK, ['Content-Type' => $qrCode->getContentType()]);
    }
}
