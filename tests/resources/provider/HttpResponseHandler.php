<?php



// ---
// Geração de Instâncias de objetos.

function prov_instanceOf_EnGarde_Http_RequestHandler(
    $environmentType = "localtest",
    $requestMethod = "GET",
    $url = "http://aeondigital.com.br",
    $serverConfig = null,
    $domainConfig = null,
    $applicationConfig = null,
    $specialSet = "0.9.0 [alpha]",
    $debugMode = false
) {
    $serverRequest = prov_instanceOf_Http_ServerRequest_02($requestMethod, $url);
    $serverRequest = $serverRequest->withCookieParams([]);


    $response = null;
    $routeConfig = null;
    $tempAppRoutes = require(dirname(__DIR__) . "/concrete/AppRoutes.php");
    if ($requestMethod === "OPTIONS" || $requestMethod === "TRACE") {
        $response = prov_instanceOf_Http_Response();
    } else {
        $routeConfig = prov_instanceOf_EnGarde_Config_Route($tempAppRoutes["simple"]["/^\\/site\\//"][$requestMethod]);

        $routeMime = $routeConfig->negotiateMimeType(
            $serverRequest->getResponseMimes(),
            $serverRequest->getParam("_mime")
        );
        $routeConfig->setResponseMime($routeMime["mime"]);
        $routeConfig->setResponseMimeType($routeMime["mimetype"]);

        $isDownload_route = $routeConfig->getResponseIsDownload();
        $isDownload_param = $serverRequest->getParam("_download");
        if ($isDownload_param !== null) {
            $isDownload_param = ($isDownload_param === "true" || $isDownload_param === "1");
        }
        $routeConfig->setResponseIsDownload(
            (
                $isDownload_param === true ||
                (
                    ($isDownload_param === null || $isDownload_param === true) &&
                    $isDownload_route === true
                )
            )
        );

        $prettyPrint = $serverRequest->getParam("_pretty_print");
        $routeConfig->setResponseIsPrettyPrint(($prettyPrint === "true" || $prettyPrint === "1"));





        $viewData = (object)[
            "appTitle" => "Application Title",
            "viewTitle" => "View Title",
            "mimeContent" => null
        ];


        $mime = $routeConfig->getResponseMime();

        if ($mime === "html") {
            $viewData->mimeContent = "This is a HTML document.";
        }
        elseif ($mime === "xhtml") {
            $viewData->mimeContent = "This is a X/HTML document.";
        }
        elseif ($mime === "json") {
            $viewData->mimeContent = "This is a JSON document.";
        }
        elseif ($mime === "txt") {
            $routeConfig->setMasterPage(null);
            $routeConfig->setView(null);

            $viewData->subData = [
                "attr1" => "val1",
                "attr2" => "val2",
                "attr3" => "val3",
                "attr4" => 8383738,
                "attr5" => new \DateTime("2000-01-01 00:00:00"),
                "attr6" => $routeConfig
            ];
        }
        elseif ($mime === "xml") {
            $routeConfig->setMasterPage(null);
            $routeConfig->setView(null);
        }
        elseif ($mime === "csv" || $mime === "xls" || $mime === "xlsx") {
            $viewData->metaData = (object)[
                "authorName" => "Rianna Cantarelli",
                "companyName" => "Aeon Digital",
                "createdDate" => new \DateTime("2019-02-10 10:10:10"),
                "keywords" => "key1, key2, key3",
                "description" => "Teste de criação de planilhas"
            ];
            $viewData->dataTable = [
                ["nome", "email", "categoria", "cpf", "number", "data"],
                ["n1\"com aspas e acentuação", "email@1", "cat1", "cpf1", 1, new \DateTime("2001-01-01 01:01:01")],
                ["n2", "email@2", "cat2", "cpf2", 2, new \DateTime("2002-02-02 02:02:02")],
                ["n3", "email@3", "cat3", "cpf3", 3, new \DateTime("2003-03-03 03:03:03")],
                ["n4", "email@4", "cat4", "cpf4", 4, new \DateTime("2004-04-04 04:04:04")]
            ];
        }
        elseif ($mime === "pdf") {
            $routeConfig->setMasterPage("masterPagePDF.phtml");
            $routeConfig->setView("home/indexPDF.phtml");
            $viewData->mimeContent = "This is a PDF document.";
            $viewData->metaData = (object)[
                "authorName" => "Rianna Cantarelli",
                "companyName" => "Aeon Digital",
                "createdDate" => new \DateTime("2019-02-10 10:10:10"),
                "keywords" => "key1, key2, key3",
                "description" => "Teste de criação de planilhas"
            ];
        }


        $response = prov_instanceOf_Http_Response_02(
            null,
            null,
            $viewData,
            null
        );
    }


    if ($serverConfig === null) {
        $serverConfig = prov_instanceOf_EnGarde_Config_Server(
            true, null, null, null, null, $requestMethod, $serverRequest->getUri()->getRelativeUri()
        );
        $httpFactory = prov_instanceOf_Http_Factory();
        $serverConfig->setHttpFactory($httpFactory);
    }

    if ($domainConfig === null) {
        $domainConfig = prov_instanceOf_EnGarde_Config_Domain(
            true, $specialSet, $environmentType, $debugMode, false, to_system_path(dirname(__DIR__) . "/apps")
        );
        $domainConfig->setPathToErrorView("errorView.phtml");
    }

    if ($applicationConfig === null) {
        $applicationConfig = prov_instanceOf_EnGarde_Config_Application_autoSet(
            true,
            "site",
            to_system_path(dirname(__DIR__) . "/apps")
        );
        $applicationConfig->setLocales(["pt-BR", "en-US"]);
        $applicationConfig->setDefaultLocale("pt-BR");
    }



    if ($routeConfig !== null) {
        // Verifica qual locale deve ser usado para responder
        // esta requisição
        $useLocale = $routeConfig->negotiateLocale(
            $serverRequest->getResponseLocales(),
            $serverRequest->getResponseLanguages(),
            $applicationConfig->getLocales(),
            $applicationConfig->getDefaultLocale(),
            $serverRequest->getParam("_locale")
        );
        $routeConfig->setResponseLocale($useLocale);



        // Verifica qual mimetype deve ser usado para responder
        // esta requisição
        $routeMime = $routeConfig->negotiateMimeType(
            $serverRequest->getResponseMimes(),
            $serverRequest->getParam("_mime")
        );
        $routeConfig->setResponseMime($routeMime["mime"]);
        $routeConfig->setResponseMimeType($routeMime["mimetype"]);


        // Identifica se é para usar "pretty print" no código fonte de retorno
        $prettyPrint = $serverRequest->getParam("_pretty_print");
        $routeConfig->setResponseIsPrettyPrint(($prettyPrint === "true" || $prettyPrint === "1"));
    }



    return new \AeonDigital\EnGarde\ResponseHandler(
        $serverConfig,
        $domainConfig,
        $applicationConfig,
        $serverRequest,
        $tempAppRoutes["simple"]["/^\\/site\\//"],
        $routeConfig,
        $response
    );
}







function prov_instanceOf_Http_Response_02(
    $headers = null,
    $strBody = null,
    $viewData = null,
    $viewConfig = null
) {
    if ($headers === null) {
        $headers = [];
    }

    if ($strBody === null) {
        $strBody = "";
    }

    if ($viewData === null) {
        $viewData = null;
    }

    if ($viewConfig === null) {
        $viewConfig = null;
    }


    return new \AeonDigital\Http\Message\Response(
        200,
        "",
        "1.1",
        provider_PHPHTTPData_InstanceOf_HeaderCollection($headers),
        provider_PHPStream_InstanceOf_Stream_FromText($strBody),
        $viewData,
        $viewConfig
    );
}
