<?php
declare (strict_types=1);

namespace AeonDigital\EnGarde\SessionControl;

use AeonDigital\BObject as BObject;
use AeonDigital\EnGarde\Interfaces\Engine\iSession as iSession;
use AeonDigital\EnGarde\SessionControl\Enum\LoginStatus as LoginStatus;
use AeonDigital\EnGarde\SessionControl\Enum\BrowseStatus as BrowseStatus;
use AeonDigital\EnGarde\SessionControl\Enum\TypeOfActivity as TypeOfActivity;
use AeonDigital\Interfaces\Http\Data\iCookie as iCookie;



/**
 * Implementa o controle de sessão para tipo "local".
 *
 * @package     AeonDigital\EnGarde
 * @author      Rianna Cantarelli <rianna@aeondigital.com.br>
 * @copyright   2020, Rianna Cantarelli
 * @license     ADPL-v1.0
 */
final class Local extends BObject implements iSession
{
    use \AeonDigital\Traits\MainCheckArgumentException;





    /**
     * Informações de um usuário.
     *
     * @var         ?array
     */
    private ?array $userData = null;
    /**
     * Retorna os dados de um usuário que esteja carregado no momento.
     *
     * @return      ?array
     */
    public function retrieveUserData() : ?array
    {
        return $this->userData;
    }



    /**
     * Status atual do login do UA.
     */
    private string $loginStatus = LoginStatus::Anonimous;
    /**
     * Retorna o status atual do login do UA.
     *
     * @return      string
     */
    public function retrieveLoginStatus() : string
    {
        return $this->loginStatus;
    }



    /**
     * Status atual da navegação do UA.
     */
    private string $browseStatus = BrowseStatus::Unchecked;
    /**
     * Retorna o status atual da navegação do UA.
     *
     * @return      string
     */
    public function retrieveBrowseStatus() : string
    {
        return $this->browseStatus;
    }





    /**
     * array associativo contendo as informações de segurança
     * exigidas para identificar e autenticar um UA em uma requisição.
     *
     * Nesta implementação são esperadas as seguintes chaves:
     * ```
     * $arr = [
     *  "IP"                    : (string)  IP do UA
     *  "SecurityCookie         : (iCookie) Objeto ``iCookie`` para o cookie de segurança.
     *  "PathToLocalData"       : (string)  Caminho completo até o diretório onde os dados da aplicação
     *                                      estão armazenados.
     *  "Now"                   : (DateTime)Data e hora do recebimento da requisição.
     *  "Environment"           : (string)  Tipo de ambiente que a aplicação está sendo executada.
     *  "ApplicationName"       : (string)  Nome da aplicação.
     *  "UserAgent"             : (string)  User Agent que efetuou a requisição.
     *  "SessionRenew"          : (bool)    Indica se a sessão é do tipo que se renova a cada requisição.
     *  "SessionTimeout"        : (int)     Tempo (em minutos) que cada sessão deve suportar de inatividade.
     *  "AllowedFaultByIP"      : (int)     Limite de falhas de login permitidas para um mesmo ``IP``.
     *  "IPBlockTimeout"        : (int)     Tempo (em minutos) de bloqueio para um ``IP`` suspeito.
     *  "AllowedFaultByLogin"   : (int)     Limite de falhas sucessivos de senha para um mesmo login.
     *  "LoginBlockTimeout"     : (int)     Tempo (em minutos) de bloqueio para um Login.
     * ];
     * ```
     */
    private array $uaSecurityData = [];
    /**
     * Caminho completo até o diretório de dados da aplicação.
     *
     * @var         string
     */
    private string $pathToLocalData = "";
    /**
     * Caminho completo até o diretório que armazena os usuários da aplicação.
     *
     * @var         string
     */
    private string $pathToLocalData_Users = "";
    /**
     * Caminho completo até o diretório que armazena as sessões ativas da aplicação.
     *
     * @var         string
     */
    private string $pathToLocalData_Sessions = "";
    /**
     * Caminho completo até o diretório que armazena os logs de atividades da aplicação.
     *
     * @var         string
     */
    private string $pathToLocalData_Log = "";


    /**
     * Caminho completo até o diretório que armazena os logs de atividades consideradas
     * suspeitas por esta implementação.
     *
     * @var         string
     */
    private string $pathToLocalData_LogSuspect = "";
    /**
     * Caminho completo até o arquivo que armazena os registros de atividade suspeita
     * vindos de um IP.
     *
     * @var         string
     */
    private string $pathToLocalData_LogFile_IP = "";
    /**
     * Caminho completo até o arquivo que armazena os registros de atividade suspeita
     * vindos de um login em especial.
     *
     * @var         string
     */
    private string $pathToLocalData_LogFile_UserName = "";
    /**
     * Caminho completo até o arquivo que armazena os dados de sessão de um usuário.
     *
     * @var         string
     */
    private string $pathToLocalData_Session_UserName = "";
    /**
     * Cookie de segurança.
     *
     * @var         iCookie
     */
    private iCookie $securityCookie;




    /**
     * Permite definir um array associativo contendo as informações de segurança
     * exigidas para identificar e autenticar um UA em uma requisição.
     *
     * Nesta implementação são esperadas as seguintes chaves:
     * $arr = [
     *  "IP"                    : (string)  IP do UA
     *  "SecurityCookie         : (iCookie) Objeto ``iCookie`` para o cookie de segurança.
     *  "PathToLocalData"       : (string)  Caminho completo até o diretório onde os dados da aplicação
     *                                      estão armazenados.
     *  "Now"                   : (DateTime)Data e hora do recebimento da requisição.
     *  "Environment"           : (string)  Tipo de ambiente que a aplicação está sendo executada.
     *  "ApplicationName"       : (string)  Nome da aplicação.
     *  "UserAgent"             : (string)  User Agent que efetuou a requisição.
     *  "SessionRenew"          : (bool)    Indica se a sessão é do tipo que se renova a cada requisição.
     *  "SessionTimeout"        : (int)     Tempo (em minutos) que cada sessão deve suportar de inatividade.
     *  "AllowedFaultByIP"      : (int)     Limite de falhas de login permitidas para um mesmo ``IP``.
     *  "IPBlockTimeout"        : (int)     Tempo (em minutos) de bloqueio para um ``IP`` suspeito.
     *  "AllowedFaultByLogin"   : (int)     Limite de falhas sucessivos de senha para um mesmo login.
     *  "LoginBlockTimeout"     : (int)     Tempo (em minutos) de bloqueio para um Login.
     * ];
     *
     * @param       array $uaSecurityData
     *              Array associativo com as informações de segurança.
     *
     * @return      void
     */
    public function setUASecurityData(array $uaSecurityData) : void
    {
        $this->mainCheckForInvalidArgumentException(
            "uaSecurityData", $uaSecurityData, [
                [
                    "conditions"       => "is array not empty",
                    "validate"         => "has array assoc required keys",
                    "requiredKeys"      => [
                        "IP"                    => ["is string not empty"],
                        "SecurityCookie"        => [
                            ["validate" => "is class implements interface",
                            "interface" => "AeonDigital\\Interfaces\\Http\\Data\\iCookie"]
                        ],
                        "PathToLocalData"       => ["is dir exists"],
                        "Now"                   => [
                            ["validate" => "is class implements interface",
                            "interface" => "DateTimeInterface"]
                        ],
                        "Environment"           => ["is string not empty"],
                        "ApplicationName"       => ["is string not empty"],
                        "UserAgent"             => ["is string not empty"],
                        "SessionRenew"          => ["is boolean"],
                        "SessionTimeout"        => ["is integer greather than zero"],
                        "AllowedFaultByIP"      => ["is integer greather than zero"],
                        "IPBlockTimeout"        => ["is integer greather than zero"],
                        "AllowedFaultByLogin"   => ["is integer greather than zero"],
                        "LoginBlockTimeout"     => ["is integer greather than zero"],
                    ]
                ]
            ]
        );
        $this->uaSecurityData = $uaSecurityData;
        $this->securityCookie = $uaSecurityData["SecurityCookie"];


        // Define os locais fisicos onde estão os dados de segurança da aplicação.
        $this->pathToLocalData          = \to_system_path($uaSecurityData["PathToLocalData"]);
        $this->pathToLocalData_Log      = $this->pathToLocalData . DS . "log";
        $this->pathToLocalData_Users    = $this->pathToLocalData . DS . "users";
        $this->pathToLocalData_Sessions = $this->pathToLocalData . DS . "sessions";


        // Testa cada valor obrigatório
        $this->mainCheckForInvalidArgumentException(
            "pathToLocalData", $this->pathToLocalData_Log, [
                ["validate"         => "is dir exists"]
            ]
        );
        $this->mainCheckForInvalidArgumentException(
            "pathToLocalData", $this->pathToLocalData_Users, [
                ["validate"         => "is dir exists"]
            ]
        );
        $this->mainCheckForInvalidArgumentException(
            "pathToLocalData", $this->pathToLocalData_Sessions, [
                ["validate"         => "is dir exists"]
            ]
        );


        $fileIP = \str_replace([".", ":"], "_", $uaSecurityData["IP"]) . ".json";
        $this->pathToLocalData_LogSuspect = $this->pathToLocalData_Log . DS . "suspect";
        $this->pathToLocalData_LogFile_IP = $this->pathToLocalData_LogSuspect . DS . $fileIP;

        if ($this->securityCookie->)
    }










    /**
     * Renova a sessão do usuário.
     *
     * @param       int $sessionTimeout
     *              Tempo (em minutos) em que a sessão deve ser expandida.
     *
     * @return      bool
     *              Retornará ``true`` caso a ação tenha sido bem sucedida, ``false``
     *              se houver alguma falha no processo.
     */
    public function renewSession(int $sessionTimeout) : bool
    {

    }



    /**
     * A partir do hash de autenticação da sessão do UA, carrega os dados da sessão caso ela
     * ainda esteja válida.
     *
     * @param       string $sessionHash
     *              Hash de autenticação da sessão do UA.
     *
     * @return      bool
     */
    public function loadSessionData(string $sessionHash) : bool
    {
        // Prosseguir daqui! <-------------------
    }
    /**
     * Carrega as informações do usuário de ``userName`` indicado.
     *
     * @param       string $userName
     *              Nome do usuário.
     *
     * @return      bool
     */
    public function loadUserData(string $userName) : bool
    {
        $userDataFile = $this->pathToLocalData_Users . DS . $userName . ".json";
        if (\file_exists($userDataFile) === true) {
            $this->userData = \AeonDigital\Tools\JSON::retrieve($userDataFile);

            if ($this->userData !== null) {
                $this->userData["ProfileInUse"] = null;
                $this->userData["Session"] = null;

                foreach ($this->userData["Profiles"] as $row) {
                    if ($this->uaSecurityData["ApplicationName"] === $row["Application"]) {
                        if ($this->userData["ProfileInUse"] === null && $row["Default"] === true) {
                            $this->userData["ProfileInUse"] = $row;
                        }
                    }
                }


                $fileUserName = \mb_str_to_valid_filename(\strtolower($userName)) . ".json";
                $this->pathToLocalData_LogFile_UserName = \to_system_path(
                    $this->pathToLocalData_LogSuspect . DS . $fileUserName
                );
            }
        }

        return ($this->userData !== null);
    }





    /**
     * Identifica se o IP do UA está liberado para uso na aplicação.
     *
     * @return      bool
     */
    public function checkValidIP() : bool
    {
        $r = true;

        if(\file_exists($this->pathToLocalData_LogFile_IP)) {
            $suspectData = \AeonDigital\Tools\JSON::retrieve($this->pathToLocalData_LogFile_IP);

            if($suspectData["Blocked"] === true) {
                $unblockDate = \DateTime::createFromFormat("Y-m-d H:i:s", $suspectData["UnblockDate"]);

                if($unblockDate < $this->uaSecurityData["Now"]) {
                    \unlink($this->pathToLocalData_LogFile_IP);
                }
                else {
                    $r = false;
                    $this->browseStatus = BrowseStatus::BlockedIP;
                    $this->loginStatus  = LoginStatus::BlockedIP;
                }
            }
        }

        return $r;
    }
    /**
     * Verifica se o usuário informado existe e está apto a receber autenticação para a
     * aplicação corrente.
     *
     * @param       string $userName
     *              Nome do usuário.
     *
     * @return      bool
     */
    public function checkUserName(string $userName) : bool
    {
        $r = false;

        if ($this->loadUserData($userName) === false) {
            $this->registerSuspectActivity(
                $userName,
                $this->pathToLocalData_LogFile_IP,
                $this->uaSecurityData["AllowedFaultByIP"],
                $this->uaSecurityData["IPBlockTimeout"],
                "IP"
            );
        }
        else {

            if($this->userData["Active"] === false) {
                $this->loginStatus = LoginStatus::AccountDisabledForDomain;
            }
            else {
                if ($this->userData["ProfileInUse"] === null) {
                    $this->loginStatus = LoginStatus::AccountDoesNotExistInApplication;
				}
                else if ($this->userData["ProfileInUse"]["Active"] === false) {
                    $this->loginStatus = LoginStatus::AccountDisabledForApplication;
				}
                else {
                    $r = true;
                    $this->loginStatus = LoginStatus::AccountRecognizedAndActive;


                    if(\file_exists($this->pathToLocalData_LogFile_UserName) === true) {
                        $loginSuspectData = \AeonDigital\Tools\JSON::retrieve($this->pathToLocalData_LogFile_UserName);

                        if($loginSuspectData !== null && $loginSuspectData["Blocked"] === true) {
                            $unblockDate = \DateTime::createFromFormat("Y-m-d H:i:s", $loginSuspectData["UnblockDate"]);

                            if($unblockDate < $this->uaSecurityData["Now"]) {
                                \unlink($this->pathToLocalData_LogFile_UserName);
                            }
                            else {
                                $r = false;
                                $this->loginStatus = LoginStatus::BlockedUser;
                            }
                        }
                    }
                }
            }
        }

        return $r;
    }
    /**
     * Verifica se a senha do usuário confere.
     *
     * @param       string $userPassword
     *              Senha do usuário.
     *
     * @return      bool
     */
    public function checkUserPassword(string $userPassword) : bool
    {
        $r = false;

        if ($this->userData !== null) {
            if ($this->userData["Password"] === $userPassword) {
                $r = true;
                $this->loginStatus = LoginStatus::WaitingApplicationAuthenticate;
            }
            else {
                $this->registerSuspectActivity(
                    $this->userData["Login"],
                    $this->pathToLocalData_LogFile_UserName,
                    $this->uaSecurityData["AllowedFaultByIP"],
                    $this->uaSecurityData["IPBlockTimeout"],
                    "UserName"
                );
            }
        }

        return $r;
    }





    /**
     * Gerencia a criação de um arquivo de controle de acessos de IP/UserName e efetua a contagem de
     * falhas de autenticação em uma atividade de verificação de "UserName" ou "UserPassword" além de
     * definir o IP/UserName como bloqueado em casos em que atinja o limite de falhas permitidas.
     *
     * @param       string $userName
     *              Nome do usuário.
     *
     * @param       string $registerFile
     *              Arquivo que deve ser usado para registrar as atividades monitoradas.
     *
     * @param       int $allowedFault
     *              Numero máximo de falhas permitidas para este tipo de ação.
     *
     * @param       int $blockTimeout
     *              Tempo (em minutos) que o IP/UserName será bloqueado em caso de atinjir o número máximo
     *              de falhas configuradas.
     *
     * @param       string $blockType
     *              Tipo de bloqueio possível.
     *              ["IP", "UserName"]
     *
     * @return      void
     */
    private function registerSuspectActivity(
        string $userName,
        string $registerFile,
        int $allowedFault,
        int $blockTimeout,
        string $blockType
    ) : void {
        $this->loginStatus = (
            ($blockType === "IP") ?
            LoginStatus::AccountDoesNotExist :
            LoginStatus::UnexpectedPassword
        );


        if (\file_exists($registerFile) === false) {
            \AeonDigital\Tools\JSON::save(
                $registerFile, [
                    "Activity"          => TypeOfActivity::MakeLogin,
                    "IP"                => $this->uaSecurityData["IP"],
                    "Login"             => ($blockType === "IP") ? "" : $userName,
                    "Counter"           => 1,
                    "LastEventDateTime" => $this->uaSecurityData["Now"]->format("Y-m-d H:i:s"),
                    "Blocked"           => false,
                    "UnblockDate"       => null
                ]
            );
        }
        else {
            $suspectData = \AeonDigital\Tools\JSON::retrieve($registerFile);
            if ($suspectData === null) {
                $suspectData = [
                    "Activity"          => TypeOfActivity::MakeLogin,
                    "IP"                => $this->uaSecurityData["IP"],
                    "Login"             => ($blockType === "IP") ? "" : $userName,
                    "Counter"           => 1,
                    "LastEventDateTime" => $this->uaSecurityData["Now"]->format("Y-m-d H:i:s"),
                    "Blocked"           => false,
                    "UnblockDate"       => null
                ];
            }
            else {
                $diffInMinutes = $this->uaSecurityData["Now"]->diff(
                    \DateTime::createFromFormat("Y-m-d H:i:s", $suspectData["LastEventDateTime"])
                )->format("%i");

                $suspectData["Counter"]++;
                $suspectData["LastEventDateTime"] = $this->uaSecurityData["Now"]->format("Y-m-d H:i:s");
                if ($diffInMinutes > $blockTimeout) {
                    $suspectData["Counter"] = 1;
                }

                if($suspectData["Counter"] >= $allowedFault) {
                    $unblockDate = new \DateTime();
                    $unblockDate->add(new \DateInterval("PT" . $blockTimeout . "M"));

                    $suspectData["Blocked"]       = true;
                    $suspectData["UnblockDate"]   = $unblockDate->format("Y-m-d H:i:s");

                    $this->loginStatus = (
                        ($blockType === "IP") ?
                        LoginStatus::YourIPIsBlocked :
                        LoginStatus::AccountIsBlocked
                    );
                }
            }

            \AeonDigital\Tools\JSON::save($registerFile, $suspectData);
        }
    }





    /**
     * Inicia os sets de segurança necessários para que uma sessão autenticada possa iniciar.
     *
     * @return      bool
     *              Retornará ``true`` caso a ação tenha sido bem sucedida, ``false``
     *              se houver alguma falha no processo.
     */
    public function inityAuthenticatedSession() : bool
    {
        $r = false;

        if ($this->userData !== null &&
            $this->loginStatus === LoginStatus::WaitingApplicationAuthenticate)
        {
            $this->loginStatus = LoginStatus::LoginFail;

            $userLogin      = $this->userData["Login"];
            $userProfile    = $this->userData["ProfileInUse"]["Profile"];
            $userLoginDate  = $this->uaSecurityData["Now"]->format("Y-m-d H:i:s");
            $sessionHash    = sha1($userLogin . $userProfile . $userLoginDate);

            $this->securityCookie->setValue(
                "userLogin=" . $userLogin .
                "&profileInUse=" . $userProfile .
                "&sessionHash=" . $sessionHash
            );

            $expiresDate = new \DateTime();
            $expiresDate->add(new \DateInterval("PT" . $this->uaSecurityData["SessionTimeout"] . "M"));
            $this->securityCookie->setExpires($expiresDate);


            if ($this->uaSecurityData["Environment"] === "UTEST" ||
                $this->securityCookie->defineCookie() === true)
            {
                $fileUserSession = $userLogin . "_" . $sessionHash . ".json";
                $this->pathToLocalData_Session_UserName = $this->pathToLocalData_Sessions . DS . $fileUserSession;

                $this->CloseAuthenticatedSession();

                $this->userData["Session"] = [
                    "Hash"          => $sessionHash,
                    "Application"   => $this->uaSecurityData["ApplicationName"],
                    "LoginDate"     => $this->uaSecurityData["Now"]->format("Y-m-d H:i:s"),
                    "TimeOut"       => $expiresDate->format("Y-m-d H:i:s"),
                    "Renew"         => $this->uaSecurityData["SessionRenew"],
                    "Login"         => $this->userData["Login"],
                    "Profile"       => $this->userData["ProfileInUse"]["Profile"],
                    "UserAgent"     => $this->uaSecurityData["UserAgent"],
                    "IP"            => $this->uaSecurityData["IP"],
                ];

                if (\AeonDigital\Tools\JSON::save(
                        $this->pathToLocalData_Session_UserName,
                        $this->userData["Session"]) === true
                ) {
                    $r = true;
                    $this->loginStatus = LoginStatus::Authorized;
                    $this->browseStatus = BrowseStatus::Authorized;
                }
            }
        }

        return $r;
    }
    /**
     * Encerra a sessão autenticada do usuário.
     *
     * @return      bool
     *              Retornará ``true`` caso a ação tenha sido bem sucedida, ``false``
     *              se houver alguma falha no processo.
     */
    public function closeAuthenticatedSession() : bool
    {
        $r = false;
        if (\is_file($this->pathToLocalData_Session_UserName) === true) {
            $r = \unlink($this->pathToLocalData_Session_UserName);
        }
        return $r;
    }





    /**
     * Gera um registro de atividade do usuário.
     *
     * @param       array $logRegistryData
     *              Dados que serão usados para preencher o log de atividade.
     *
     * @return      bool
     *              Retornará ``true`` caso a ação tenha sido bem sucedida, ``false``
     *              se houver alguma falha no processo.
     */
    public function registerLogActivity(array $logRegistryData) : bool
    {

    }





    /**
     * Identifica se o usuário atualmente autenticado tem ou não permissão de acessar
     * a rota atual.
     *
     * @return      bool
     */
    public function checkPermissionForRoute() : bool
    {

    }
}
