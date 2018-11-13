<?php
declare (strict_types = 1);

use PHPUnit\Framework\TestCase;
use AeonDigital\Http\Tools\ServerConfig as ServerConfig;
use AeonDigital\EnGarde\ErrorListening as ErrorListening;
use AeonDigital\EnGarde\DomainManager as DomainManager;

require_once __DIR__ . "/../phpunit.php";


// Ajustar os testes daqui para funcionarem quando executados em lote.
// Prosseguir com o processamento da rota em si

class DomainManagerTest extends TestCase
{





    protected $rootPath = null;
    protected $domainConfigFileName = "domain-config-local.php";


    /**
     * Retorna um objeto "ServerConfig" para os testes.
     *
     * @return      ServerConfig
     */
    protected function retrieveServerConfigToTest(
        $autoSet = true,
        $serverIP = "200.200.100.50",
        $serverDomain = "test.server.com.br",
        $serverPort = 80,
        $serverMethod = "GET",
        $serverURI = "/",
        $serverURIQS = null,
        $serverScript = "/index.php",
        $userAgend = "Mozilla\/5.0 (Windows NT 10.0; Win64; x64; rv:56.0) Gecko\/20100101 Firefox\/56.0",
        $accept = "text\/html, application\/xhtml+xml, application\/xml;q=0.9, *\/*;q=0.8",
        $acceptLanguage = "pt-BR, pt;q=0.8, en-US;q=0.5, en;q=0.3",
        $acceptEncoding = "gzip, deflate"
    ) : ServerConfig {
        $nMock = new ServerConfig(null, true);
        $nMock->setHttpTools(new \AeonDigital\Http\Tools\Tools());
        $nMock->setServerVariables(null);
        
        $this->rootPath = to_system_path(__DIR__ . DIRECTORY_SEPARATOR . "apps");
        require_once $this->rootPath . DIRECTORY_SEPARATOR . $this->domainConfigFileName;
        require_once $this->rootPath . DIRECTORY_SEPARATOR . "site/AppStart.php";
        require_once $this->rootPath . DIRECTORY_SEPARATOR . "site/controllers/Home.php";

        if ($autoSet === true) {
            

            $testServer = [
                "DOCUMENT_ROOT" => $this->rootPath,
                "REMOTE_ADDR" => $serverIP,
                "REMOTE_PORT" => "64011",
                "SERVER_SOFTWARE" => "PHP 7.1.1 Development Server",
                "SERVER_PROTOCOL" => "HTTP/1.1",
                "SERVER_NAME" => $serverDomain,
                "SERVER_PORT" => $serverPort,
                "REQUEST_METHOD" => $serverMethod,
                "REQUEST_URI" => $serverURI,
                "SCRIPT_NAME" => $serverScript,
                "SCRIPT_FILENAME" => $this->rootPath . $serverScript,
                "QUERY_STRING" => $serverURIQS,
                "PHP_SELF" => $serverScript,
                "HTTP_HOST" => $serverDomain,
                "HTTP_COOKIE" => "first=primeiro+valor%3A+rianna%40gmail.com; second=segundo+valor%3A+http%3A%2F%2Faeondigital.com.br",
                "HTTP_USER_AGENT" => $userAgend,
                "HTTP_ACCEPT" => $accept,
                "HTTP_ACCEPT_LANGUAGE" => $acceptLanguage,
                "HTTP_ACCEPT_ENCODING" => $acceptEncoding,
                "HTTP_CONNECTION" => "keep-alive",
                "HTTP_UPGRADE_INSECURE_REQUESTS" => "1",
                "REQUEST_TIME_FLOAT" => 1507429648.193122,
                "REQUEST_TIME" => 1507429648,
                "CONTENT_TYPE" => "text/html; charset=utf-8",
                "CONTENT_LENGTH" => 1500,
                "PHP_AUTH_USER" => 1,
                "PHP_AUTH_PW" => 1,
                "PHP_AUTH_DIGEST" => 1,
                "AUTH_TYPE" => 1
            ];

            $nMock->setServerVariables($testServer);
            $this->server = $testServer;
        }

        return $nMock;
    }

    protected function executeRequest(
        $serverMethod = "GET", 
        $serverURI = "/", 
        $querystring = null
    ) : ServerConfig {
        return $this->retrieveServerConfigToTest(true, "200.200.100.50", "test.server.com.br", 80, $serverMethod, $serverURI, $querystring);
    }





    public function test_constructor_register_errorlistening()
    {
        $serverConfig = $this->retrieveServerConfigToTest(true);
        $enGarde = new DomainManager($serverConfig);
        $this->assertTrue(is_a($enGarde, DomainManager::class));


        $expected = [
            "rootPath"          => $this->rootPath . DS,
            "environmentType"   => "local",
            "isDebugMode"       => true,
            "protocol"          => "http",
            "method"            => "GET",
            "pathToErrorView"   => $this->rootPath . DS . "errorView.phtml"
        ];

        $errorContext = ErrorListening::getContext();
        $this->assertSame($expected, $errorContext);
    }



    public function test_constructor_register_routes()
    {
        $serverConfig = $this->retrieveServerConfigToTest(true);

        $pathToAppRoutes = $this->rootPath . DS . "site/AppRoutes.php";
        if (file_exists($pathToAppRoutes) === true) {
            unlink($pathToAppRoutes);
        }

        $enGarde = new DomainManager($serverConfig);
        $enGarde->prepareRouteBeforeRun();
        
        $this->assertTrue(is_a($enGarde, DomainManager::class));
        $this->assertTrue(file_exists($pathToAppRoutes));
    }



    public function test_method_run()
    {
        $serverConfig = $this->retrieveServerConfigToTest(true);
        $enGarde = new DomainManager($serverConfig);
        $this->assertTrue(is_a($enGarde, DomainManager::class));

        $enGarde->run();

        $expected = [
            "rootPath"          => to_system_path($this->rootPath) . DS,
            "environmentType"   => "local",
            "isDebugMode"       => true,
            "protocol"          => "http",
            "method"            => "GET",
            "pathToErrorView"   => to_system_path($this->rootPath . DS . "site/views/_shared/errorView.phtml")
        ];
        
        $errorContext = ErrorListening::getContext();
        $this->assertSame($expected, $errorContext);
    }



    public function test_check_response_to_error_404()
    {
        $this->domainConfigFileName = "domain-config-testview.php";
        $serverConfig   = $this->executeRequest("GET", "/non-exist-route/for/this");
        $enGarde        = new DomainManager($serverConfig);
        $this->assertTrue(is_a($enGarde, DomainManager::class));
        
        $enGarde->run();
        $output = $enGarde->getTestViewDebug();

        $tgtPathToExpected  = __DIR__ . DIRECTORY_SEPARATOR . "expectedresponses/error404.html";
        $expected           = file_get_contents($tgtPathToExpected);

        //file_put_contents($tgtPathToExpected, $output);
        $this->assertSame($expected, $output);
    }



    public function test_check_response_to_error_501()
    {
        $this->domainConfigFileName = "domain-config-testview.php";
        $serverConfig   = $this->executeRequest("PUT", "/");
        $enGarde        = new DomainManager($serverConfig);
        $this->assertTrue(is_a($enGarde, DomainManager::class));
        
        $enGarde->run();
        $output = $enGarde->getTestViewDebug();

        $tgtPathToExpected  = __DIR__ . DIRECTORY_SEPARATOR . "expectedresponses/error501.html";
        $expected           = file_get_contents($tgtPathToExpected);

        //file_put_contents($tgtPathToExpected, $output);
        $this->assertSame($expected, $output);
    }



    public function test_check_response_to_OPTIONS()
    {
        $this->domainConfigFileName = "domain-config-testview.php";
        $serverConfig   = $this->executeRequest("OPTIONS", "/");
        $enGarde        = new DomainManager($serverConfig);
        $this->assertTrue(is_a($enGarde, DomainManager::class));
        
        $enGarde->run();
        $output = $enGarde->getTestViewDebug();

        $tgtPathToExpected  = __DIR__ . DIRECTORY_SEPARATOR . "expectedresponses/responseOPTIONS.json";
        $expected           = file_get_contents($tgtPathToExpected);

        $objExpected    = json_decode($expected);
        $objOutput      = json_decode((string)$output->getBody());

        $objExpected->Date = $objOutput->Date;

        //file_put_contents($tgtPathToExpected, (string)$output->getBody());
        $this->assertEquals($objExpected, $objOutput);
    }



    public function test_check_response_to_TRACE()
    {
        $this->domainConfigFileName = "domain-config-testview.php";
        $serverConfig   = $this->executeRequest("TRACE", "/home?q1=p1&q2=v2#tothis");
        $enGarde        = new DomainManager($serverConfig);
        $this->assertTrue(is_a($enGarde, DomainManager::class));
        
        $enGarde->run();
        $output = $enGarde->getTestViewDebug();

        $tgtPathToExpected  = __DIR__ . DIRECTORY_SEPARATOR . "expectedresponses/responseTRACE.json";
        $expected           = file_get_contents($tgtPathToExpected);

        $objExpected    = json_decode($expected);
        $objOutput      = json_decode((string)$output->getBody());

        //file_put_contents($tgtPathToExpected, (string)$output->getBody());
        $this->assertEquals($objExpected->requestData, $objOutput->requestData);
    }
}
