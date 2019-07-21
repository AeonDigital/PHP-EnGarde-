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
 * Manipulador para gerar documentos XLS.
 * 
 * @package     AeonDigital\EnGarde
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @license     GNUv3
 * @copyright   Aeon Digital
 */
class XLS extends aMimeHandler
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
        $viewData = $this->response->getViewData();
        $dataTable = ((isset($viewData->dataTable) === true) ? $viewData->dataTable : []);
        $finalArray = $this->prepareArrayToCreateSpreadSheet($dataTable);
        return $this->createXLSBody($finalArray);
    }





    /**
     * A partir do array que representa a planilha a ser criada,
     * gera uma string compatível com o formato XLS.
	 * 
	 * Baseado no original:
	 * https://gist.github.com/samatsav/6637984
     * 
     * @param       array $dataTable
     *              Array de arrays contendo cada uma das linhas de
     *              dados a ser usado na planilha.
     * 
     * @return      string
     */
    private function createXLSBody(array $dataTable) : string
    {
        // excell BOF
		$str = pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);

        $rowNumber = 0;
        foreach ($dataTable as $rowData) {
            
            $columnNumber = 0;
            foreach($rowData as $value) {
                if (is_numeric($value) === true) {
                    $str .= $this->numberCell($rowNumber, $columnNumber, $value);
                } 
                else {
                    $str .= $this->textCell($rowNumber, $columnNumber, $value);
                }

                $columnNumber++;
            }

            $rowNumber++;
        }

        // excell EOF
        $str .= pack("ss", 0x0A, 0x00);

        return $str;        
    }



    /**
     * Gera o código a ser usado para uma célula de texto
     * para uma planilha Excell (xls).
     *
     * @param       int $rowNumber
     *              Número da linha que a célula pertence.
     * 
     * @param       int $columnNumber
     *              Número da coluna que a célula pertence.
     * 
     * @param       string $value
     *              Valor que será adicionado na célula.
     * 
     * @return      string
     */
    private function textCell(int $rowNumber, int $columnNumber, string $value) : string 
    {
        $value = utf8_decode($value);
		$length = strlen($value);
		$str = pack("ssssss", 0x204, 8 + $length, $rowNumber, $columnNumber, 0x0, $length);
		$str .= $value;
		return $str; 
    }
    /**
     * Gera o código a ser usado para uma célula numérica
     * para uma planilha Excell (xls).
     *
     * @param       int $rowNumber
     *              Número da linha que a célula pertence.
     * 
     * @param       int $columnNumber
     *              Número da coluna que a célula pertence.
     * 
     * @param       string $value
     *              Valor que será adicionado na célula.
     * 
     * @return      void
     */
    private function numberCell(int $rowNumber, int $columnNumber, string $value) : string 
    {
        $str = pack("sssss", 0x203, 14, $rowNumber, $columnNumber, 0x0);
        $str .= pack("d", $value);
		return $str; 
    }
}
