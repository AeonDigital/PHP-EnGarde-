<?php
declare (strict_types = 1);

namespace AeonDigital\EnGarde\MimeHandler;

use AeonDigital\Http\Message\Interfaces\iServerRequest as iServerRequest;
use AeonDigital\Http\Message\Interfaces\iResponse as iResponse;
use AeonDigital\EnGarde\Config\Interfaces\iServerConfig as iServerConfig;
use AeonDigital\EnGarde\Config\Interfaces\iDomainConfig as iDomainConfig;
use AeonDigital\EnGarde\Config\Interfaces\iApplicationConfig as iApplicationConfig;
use AeonDigital\EnGarde\Config\Interfaces\iRouteConfig as iRouteConfig;
use AeonDigital\EnGarde\MimeHandler\aMimeHandler as aMimeHandler;


/**
 * Manipulador para gerar documentos XML.
 * 
 * @package     AeonDigital\EnGarde
 * @version     v0.2.2-alpha
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   GNUv3
 */
class XML extends aMimeHandler
{





    /**
     * Inicia uma nova instância.
     *
     * @param       iServerConfig $serverConfig
     *              Instância "iServerConfig".
     * 
     * @param       iDomainConfig $domainConfig
     *              Instância "iDomainConfig".
     * 
     * @param       iApplicationConfig $applicationConfig
     *              Instância "iApplicationConfig".
     * 
     * @param       iServerRequest $serverRequest
     *              Instância "iServerRequest".
     * 
     * @param       array $rawRouteConfig
     *              Instância "iServerConfig".
     * 
     * @param       iRouteConfig $routeConfig
     *              Instância "iRouteConfig".
     * 
     * @param       iResponse $response
     *              Instância "iResponse".
     */
    function __construct(
        iServerConfig $serverConfig,
        iDomainConfig $domainConfig,
        iApplicationConfig $applicationConfig,
        iServerRequest $serverRequest,
        array $rawRouteConfig,
        iRouteConfig $routeConfig,
        iResponse $response
    ) {
        parent::__construct(
            $serverConfig,
            $domainConfig,
            $applicationConfig,
            $serverRequest,
            $rawRouteConfig,
            $routeConfig,
            $response
        );
    }





    /**
     * Gera uma string que representa a resposta a ser enviada
     * para o UA, compatível com o mimetype que esta classe está
     * apta a manipular.
     * 
     * @return      string
     */
    public function createResponseBody() : string
    {
        $body = "";
        $hasTemplate = ($this->routeConfig->getView() !== null || $this->routeConfig->getMasterPage() !== null);


        if ($hasTemplate === true) {
            $viewContent    = $this->processViewContent();
            $masterContent  = $this->processMasterPageContent();

            $masterContent = (($masterContent === "") ? "<view />" : $masterContent);
            // Mescla os dados obtidos
            $body = str_replace("<view />", $viewContent, $masterContent);
        } 
        else {
            $viewData = $this->response->getViewData();
            if ($viewData !== null) {
                $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><root></root>");
                $this->convertArrayToXML((array)$viewData, $xml);
                $body = $xml->asXML();
            }
        }


        // Aplica "prettyPrint" caso seja requisitado
        if ($this->routeConfig->getResponseIsPrettyPrint() === true) {
            $body = $this->prettyPrintXMLDocument($body);
        }

        return $body;
    }
}
