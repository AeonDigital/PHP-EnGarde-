<?php
declare (strict_types = 1);

namespace site\controllers;

use AeonDigital\EnGarde\DomainController as DomainController;







/**
 * Test Controller
 */
class Home extends DomainController
{

    const defaultRouteConfig = [
        "description" => "Descrição genérica no controller.",
        "acceptmimes" => ["xhtml", "html", "txt"],
        "method" => "get",
        "isusexhtml" => true,
        "middlewares" => ["ctrl_mid_01", "ctrl_mid_02", "ctrl_mid_03"],
        "metadata" => [
            "Author" => "Aeon Digital",
            "CopyRight" => "20xx Aeon Digital",
            "FrameWork" => "PHP-AeonDigital\EnGarde 0.9.0 [alpha]"
        ]
    ];



    public static $registerRoute_GET_test = "GET /test test public no-cache";




    /**
     * [Method]      : GET
     * [Description] : Página inicial da aplicação.
     *
     * [Routes]      : /
     *                 /home
     */
    public static $registerRoute_GET_POST_default = [
        "description" => "Página home da aplicação",

        "method" => ["GET", "POST"],
        "routes" => [
            "/",
            "/home"
        ],
        "action" => "default",

        "relationedRoutes" => ["/list"],

        "isSecure" => false,
        "acceptMimes" => ["xhtml", "html", "txt", "json", "xml", "csv", "xls", "pdf"],

        "isUseCache" => false,
        "cacheTimeout" => (2 * 60),

        "responseIsDownload" => false,
        "responseDownloadFileName" => "bem_vindo",
        "middlewares" => ["route_mid_01", "route_mid_02", "route_mid_03"]
    ];
    public function default()
    {
        $this->routeConfig->setMasterPage("masterPage.phtml");
        $this->routeConfig->setView("home/index.phtml");


        $this->routeConfig->setMetaData([
            "meta01" => "val01"
        ]);

        $this->routeConfig->setJavaScripts([
            "javascript01.js",
            "javascript02.js"
        ]);

        $this->routeConfig->setStyleSheets([
            "cssfile01.css",
            "cssfile02.css"
        ]);

        $this->viewData->appTitle = "Application Title";
        $this->viewData->viewTitle = "View Titlee";
    }




    public static $registerRoute_customRun = [
        "description" => "Página home da aplicação",
        "method" => ["GET"],
        "routes" => ["/customrun"],
        "action" => "-",
        "runMethodName" => "customRun"
    ];





    /**
     * [Methods]     : GET
     * [Description] : Evoca a view de lista.
     *
     * [Routes]      : /list
     *                 /list/orderby:[a-zA-Z]+
     */
    public static $registerRoute_GET_list = [
        "description" => "Evoca a view de lista.",

        "method" => "GET",
        "routes" => [
            "/list",
            "/list/orderby:[a-zA-Z]+",
            "/list/orderby:[a-zA-Z]+/page:[0-9]"
        ],
        "action" => "list",
        "acceptMimes" => ["xhtml", "html"],
    ];
    public function list()
    {
    }





    /**
     * [Methods]     : GET  
     * [Description] : Evoca a view par ao formulário de contato.
     *
     * [Routes]      : /contact
     */
    public static $registerRoute_GET_contact = [
        "description" => "Evoca a view para o formulário de contato.",

        "method" => "GET",
        "routes" => [
            "/contact"
        ],
        "action" => "contact",
        "acceptMimes" => ["xhtml", "html"],
        "isUseCache" => false,
        //"form"              => "/Site/FormModels/contact.php"
    ];
    /**
     * [Methods]     : POST  
     * [Description] : Recebe e processa os dados recebidos pelo formulário.
     *
     * [Routes]      : /contact
     */
    public static $registerRoute_POST_contact = [
        "description" => "Recebe os dados submetidos pelo formulário de contato, processa-os e retorna o resultado.",

        "method" => "POST",
        "routes" => [
            "/contact"
        ],
        "action" => "contact",

        "acceptMimes" => ["json", "xhtml", "html"],
        "isUseCache" => false,
        //"form"              => "/Site/FormModels/contact.php"
    ];
    public function contact()
    {
    }



}
