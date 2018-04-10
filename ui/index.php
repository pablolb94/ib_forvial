<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require('../sysfrm/config.php');
require('../sysfrm/orm.php');
require('../sysfrm/autoload/Contacts.php');
require('../sysfrm/autoload/Transaction.php');
require('../sysfrm/autoload/User.php');
require('../sysfrm/autoload/Password.php');
require('../sysfrm/autoload/Invoice.php');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, *");

ORM::configure("mysql:host=$db_host;dbname=$db_name");
ORM::configure('username', $db_user);
ORM::configure('password', $db_password);
ORM::configure('driver_options', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
ORM::configure('return_result_sets', true); // returns result sets
ORM::configure('logging', true);
$data = array();
$data['success'] = false;
$data['msg'] = '';
$ip = '';
$ref = '';
$agent = '';
if(isset($_SERVER['REMOTE_ADDR'])){
$ip = $_SERVER['REMOTE_ADDR'];
}
if(isset($_SERVER['HTTP_REFERER'])){
    $ref = $_SERVER['HTTP_REFERER'];
}
if(isset($_SERVER['HTTP_USER_AGENT'])){
    $agent = $_SERVER['HTTP_USER_AGENT'];
}
$data['ip'] = $ip;
$data['ref'] = $ref;
$data['agent'] = $agent;
$data['result'] = false;
function api_response($data=array()){

    header('Content-type: application/json');
    /*
        JSONP por motivos de CrossDomain y seguridad.
    */
    echo $_GET['callback'].'('.json_encode($data).')';
    //echo json_encode( $data );
exit;
}
function api_response_p($data=array()){
    header('Content-type: application/json');
    echo json_encode( $data );
}

if(isset($_GET['key']) AND ($_GET['key'] != '')){

    $key = $_GET['key'];

    $a = ORM::for_table('sys_api')->where('apikey',$key)->find_one();
    if($a){
        $data['success'] = true;

        $method = $_GET['method'];

        switch ($method) {

            case 'create_user':
                $email = $_GET['email'];
                $account = $_GET['account'];
                $data = Contacts::create_user($email, $account);
                api_response_p($data);
                break;
            case 'getUserId':
                $email = $_GET['email'];
                $data = Contacts::getUserId($email);
                api_response_p($data);
                break;
            case 'edit_user':
                $a = array();
                $a['email'] = $_GET['email'];
                $a['name'] = $_GET['name'];
                $a['surname'] = $_GET['surname'];
                $data = Contacts::edit_user($a);
                api_response_p($data);
                break;
            case 'delete_user':
                $email = $_POST['email'];
                $data = Contacts::delete_user($email);
                api_response_p($data);
                break;
            case 'edit_user_status':
                    $a = array();
                    $a['email'] = $_POST['email'];
                    $a['estado'] = $_POST['estado'];
                    $data = Contacts::edit_user_status($a);
                    api_response_p($data);
                    break;



            case 'login':
                /*
                    Necesaria la llamada al servicio web por GET, al crearse una cookie de sesión
                    hay que implementar una llamada con jsonp
                */
                $email = $_GET['email'];
                $password = $_GET['password'];

                $l = User::login($email,$password);

                if($l){
                    $data['token'] = $l;
                }
                else{

                    $data['token'] = false;

                }


                api_response($data);




                break;


            case 'login_token':
                $email = $_POST['email'];
                $password = $_POST['password'];
                $data = User::login_token($email,$password);
                api_response_p($data);

            break;

            case 'check_token':
                $token = $_GET['token'];
                $email = $_POST['email'];
                $password = $_POST['password'];
                $data = User::checkToken($email,$password,$token);
                api_response_p($data);
            break;

            case 'logout':

                $token = _post('token');

                $l = Contacts::logout_using_token($token);

                if($l){
                    $data['logout'] = true;
                }
                else{
                    $data['logout'] = false;
                }

                api_response($data);

                break;

            case 'booking_calendars':
                $token = $_GET['token'];
                $data = User::booking_calendars($token);
                api_response_p($data);
            break;

            case 'crm_accounts':
                $token = $_GET['token'];
                $email = null;
                if(isset($_GET['email'])){
                    $email = $_GET['email'];
                }
                $data = User::crm_accounts($token,$email);
                api_response_p($data);
            break;
            case 'booking_reservations':

                if(isset($_GET['reservation_email'])){
                    $email = $_GET['reservation_email'];
                }
                $a = array();
                $a['token'] = $_GET['token'];

                if(isset($_GET['reservation_email'])){
                    $a['reservation_email'] = $_GET['reservation_email'];
                }

                if(isset($_GET['calendar_id'])){
                    $a['calendar_id'] = $_GET['calendar_id'];
                }
                if(isset($_GET['from'])){
                    $a['from'] = $_GET['from'];
                }
                if(isset($_GET['to'])){
                    $a['to'] = $_GET['to'];
                }

                $data = User::booking_reservation($a);
                api_response_p($data);
            break;
    
            case 'set_athlete_attendance':

                if(isset($_GET['reservation_email'])){
                    $email = $_GET['reservation_email'];
                }
                $a = array();
                $a['token'] = $_GET['token'];


                if(isset($_GET['reservation_id'])){
                    $a['reservation_id'] = $_GET['reservation_id'];
                }

                if(isset($_GET['assistance'])){
                    $a['assistance'] = $_GET['assistance'];
                }

                $data = User::set_athlete_attendance($a);
                api_response_p($data);
            break;
            case 'get_athlete_reservations':
                if(isset($_GET['reservation_email'])){
                    $email = $_GET['reservation_email'];
                }
                $a = array();


                if(isset($_GET['reservation_email'])){
                    $a['reservation_email'] = $_GET['reservation_email'];
                }

                if(isset($_GET['calendar_id'])){
                    $a['calendar_id'] = $_GET['calendar_id'];
                }
                if(isset($_GET['from'])){
                    $a['from'] = $_GET['from'];
                }
                if(isset($_GET['to'])){
                    $a['to'] = $_GET['to'];
                }
                if(isset($_GET['slot_id'])){
                    $a['slot_id'] = $_GET['slot_id'];
                }

                $data = User::get_athlete_reservations($a);
                api_response_p($data);
            break;

            case 'cancel_athlete_reservation':
                  $a = array();
                if(isset($_GET['reservation_email'])){
                    $email = $_GET['reservation_email'];
                }
                if(isset($_GET['reservation_id'])){
                    $a['reservation_id'] = $_GET['reservation_id'];
                }

                $data = User::cancel_athlete_reservation($a, $email);
                api_response_p($data);
            break;

            case 'update_Reservation':
                $a = array();
                $a["id"] = $_GET['id'];
                $a['val'] = $_GET['val'];

                $data = User::update_Reservation($a);
                api_response_p($data);
            break;

            case 'booking_slots':



                $a = array();
                $a['token'] = $_GET['token'];


                if(isset($_GET['slot_id'])){
                    $a['slot_id'] = $_GET['slot_id'];
                }

                if(isset($_GET['calendar_id'])){
                    $a['calendar_id'] = $_GET['calendar_id'];
                }
                if(isset($_GET['from'])){
                    $a['from'] = $_GET['from'];
                }
                if(isset($_GET['to'])){
                    $a['to'] = $_GET['to'];
                }

                $data = User::booking_slots($a);
                api_response_p($data);
            break;

            case 'cus_log_classes':
                $a = array();
                $a['token'] = $_GET['token'];


                if(isset($_GET['slot_id'])){
                    $a['slot_id'] = $_GET['slot_id'];
                }

                if(isset($_GET['user_id'])){
                    $a['user_id'] = $_GET['user_id'];
                }
                if(isset($_GET['from'])){
                    $a['from'] = $_GET['from'];
                }
                if(isset($_GET['to'])){
                    $a['to'] = $_GET['to'];
                }

                $data = User::booking_slots($a);
                api_response_p($data);
            break;

            case 'cus_altasBajas':
                $a = array();
                $a['token'] = $_GET['token'];

                if(isset($_GET['from'])){
                    $a['from'] = $_GET['from'];
                }
                if(isset($_GET['to'])){
                    $a['to'] = $_GET['to'];
                }
                if(isset($_GET['email'])){
                    $a['email'] = $_GET['email'];
                }

                $data = User::cusAltasBajas($a);
                api_response_p($data);
                break;

            case 'invoices':
                 $a = array();

                if(isset($_GET['account'])){
                    $a['account'] = $_GET['account'];
                }

                if(isset($_GET['date'])){
                    $a['date'] = $_GET['date'];
                }
                if(isset($_GET['status'])){
                    $a['status'] = $_GET['status'];
                }
                if(isset($_GET['from'])){
                    $a['from'] = $_GET['from'];
                }
                if(isset($_GET['to'])){
                    $a['to'] = $_GET['to'];
                }

                $data = Invoice::invoices($a);
                api_response_p($data);
            break;

            case 'addInvoice':
                $a = array();
                $a['importe'] = $_GET['importe'];
                $a['email'] = $_GET['email'];
                if(isset($_GET['notes'])){
                    $a['notes'] = $_GET['notes'];
                }else{
                    $a['notes'] = "";
                }
                $data = Invoice::addInvoice($a);
                api_response_p($data);
            break;

            case 'update_status_invoice':
              $a = array();
              $a['id'] = $_POST['id'];
              $a['status'] = $_POST['status'];

              $data = Invoice::update_status_invoice($a);
              api_response_p($data);
            break;

            case 'activity':
                 $a = array();
                 $a['token'] = $_GET['token'];
                 if(isset($_GET['id'])){
                     $a['id'] = $_GET['id'];
                 }
                $data = Item::activity($a);
                api_response_p($data);
            break;

            case 'activeActivity':
                 $a = array();
                 $a['token'] = $_GET['token'];
                 if(isset($_GET['id'])){
                     $a['id'] = $_GET['id'];
                 }
                $data = Item::activeActivity($a);
                api_response_p($data);
            break;

            case 'create_activity':
                $a = array();

                $a['id'] = $_POST['id'];
                $a['name'] = $_POST['name'];
                if(isset($_POST['description'])){
                  $a['description'] = $_POST['description'];
                }
                $data = Item::create_activity($a);
                api_response_p($data);
                break;

            case 'edit_activity':
                $a = array();
                $a['id'] = $_POST['id'];
                $a['name'] = $_POST['name'];
                if(isset($_POST['description'])){
                  $a['description'] = $_POST['description'];
                }
                $data = Item::edit_activity($a);
                api_response_p($data);
                break;

            case 'delete_activity':
                $id = $_POST['id'];
                $data = Item::delete_activity($id);
                api_response_p($data);
                break;

            case 'activitiesItems':
                 $a = array();
                 $a['token'] = $_GET['token'];

                 if(isset($_GET['id'])){
                     $a['id'] = $_GET['id'];
                 }
                 if(isset($_GET['type'])){
                     $a['type'] = $_GET['type'];
                 }
                $data = Item::activitiesItems($a);
                api_response_p($data);
            break;

            case 'activitiesItem':
                 $a = array();
                 $a['token'] = $_GET['token'];
                 if(isset($_GET['id'])){
                     $a['id'] = $_GET['id'];
                 }
                 if(isset($_GET['id_activity'])){
                     $a['id_activity'] = $_GET['id_activity'];
                 }
                 if(isset($_GET['id_item'])){
                     $a['id_item'] = $_GET['id_item'];
                 }
                $data = Item::activitiesItem($a);
                api_response_p($data);
            break;

            case 'create_activitiesItem':
                $a = array();

                $a['id'] = $_POST['id'];
                $a['idActivity'] = $_POST['idActivity'];
                $a['idItem'] = $_POST['idItem'];
                $a['activityName'] = $_POST['activityName'];
                $a['accessNumber'] = $_POST['accessNumber'];
                $a['period'] = $_POST['period'];
                if(isset($_POST['description'])) {
                  $a['description'] = $_POST['description'];
                }

                $data = Item::create_activitiesItem($a);
                api_response_p($data);
                break;

            case 'edit_activitiesItem':
                $a = array();
                $a['id'] = $_POST['id'];
                $a['idItem'] = $_POST['idItem'];
                $a['idActivity'] = $_POST['idActivity'];
                $a['activityName'] = $_POST['activityName'];
                $a['accessNumber'] = $_POST['accessNumber'];
                $a['period'] = $_POST['period'];
                $a['description'] = $_POST['description'];

                $data = Item::edit_activitiesItem($a);
                api_response_p($data);
                break;

            case 'delete_activitiesItem':
                $id = $_POST['id'];
                $data = Item::delete_activitiesItem($id);
                api_response_p($data);
                break;

            case 'items':
                 $a = array();
                 $a['token'] = $_GET['token'];

                 if(isset($_GET['id'])){
                     $a['id'] = $_GET['id'];
                 }
                 if(isset($_GET['type'])){
                     $a['type'] = $_GET['type'];
                 }
                $data = Item::items($a);
                api_response_p($data);
            break;

            case 'create_item':
                $a = array();

                $a['id'] = $_POST['id'];
                $a['name'] = $_POST['name'];
                $a['description'] = $_POST['description'];
                $a['billing_period'] = $_POST['billing_period'];
                $a['sales_price'] = $_POST['sales_price'];
                $a['access_limit'] = $_POST['access_limit'];
                $a['expiration_days'] = $_POST['expiration_days'];

                $data = Item::create_item($a);
                api_response_p($data);
                break;

            case 'edit_item':
                $a = array();
                $a['id'] = $_POST['id'];
                $a['name'] = $_POST['name'];
                $a['description'] = $_POST['description'];
                $a['billing_period'] = $_POST['billing_period'];
                $a['sales_price'] = $_POST['sales_price'];
                $a['access_limit'] = $_POST['access_limit'];
                $a['expiration_days'] = $_POST['expiration_days'];

                $data = Item::edit_item($a);
                api_response_p($data);
                break;
            case 'delete_item':
                $id = $_POST['id'];
                $data = Item::delete_item($id);
                api_response_p($data);
                break;

            case 'services':
                 $a = array();
                 $a['token'] = $_GET['token'];

                 if(isset($_GET['id'])){
                     $a['id'] = $_GET['id'];
                 }
                 if(isset($_GET['type'])){
                     $a['type'] = $_GET['type'];
                 }
                $data = Item::services($a);
                api_response_p($data);
            break;

            //METODOS PARA LA GESTION DE PRODUCTOS.
            case 'getProducts':
                $data = Products::getProducts();
                api_response_p($data);
            break;

            case 'delProduct':
                $id=$_GET["idProduct"];
                $data = Products::delProduct($id);
                api_response_p($data);
            break;

            case 'getProduct':
                $id=$_GET["idProduct"];
                $data = Products::getProduct($id);
                api_response_p($data);
            break;

            case 'editProduct':
                $nombre=$_GET["nombre"];
                $precio=$_GET["precio"];
                $desc=$_GET["desc"];
                $id=$_GET["idProduct"];
                $data = Products::editProduct($id, $nombre, $precio, $desc);
                api_response_p($data);
            break;

            case 'addProduct':
                $nombre=$_GET["nombre"];
                $precio=$_GET["precio"];
                $desc=$_GET["desc"];
                $data = Products::addProduct($nombre, $precio, $desc);
                api_response_p($data);
            break;
                //FIN METODOS PARA LA GESTION DE PRODUCTOS.
                
                //GESTION DE CUOTAS DE USUARIOS (CAMBIOS DE CUOTA EN ATHLETES)
            case 'doInactive':
                $a = array();
                $a['email']=$_GET["usermail"];
                $data = User::doInactive($a);
                api_response_p($data);
            break;

            case 'update_cuote_user':
                $a = array();
                if(isset($_GET['nd'])){
                    $a['nd'] = $_GET['nd'];
                }
                //fecha de inicio de acceso
                if(isset($_GET['fia'])){
                    $a['fia'] = $_GET['fia'];
                }
                //fecha de fin de acceso
                if(isset($_GET['ffa'])){
                    $a['ffa'] = $_GET['ffa'];
                }
                //r
                if(isset($_GET['r'])){
                    $a['r'] = $_GET['r'];
                }
                //ID ITEM
                if(isset($_GET['itemID'])){
                    $a['itemID'] = $_GET['itemID'];
                }
                $a['email']=$_GET["usermail"];
                $data = Invoice::update_cuote_user($a);
                api_response_p($data);
            break;

            //ASIGNAR ACTIVIDAD A UN BONO
            case 'asignActivityBono':
                $a = array();

                $a['id'] = $_POST['id'];
                $a['idActivity'] = $_POST['idActivity'];
                $a['idItem'] = $_POST['idItem'];
                $a['period'] = $_POST['period'];
                $a['actividades'] = $_POST['actividades'];
                
                if(isset($_POST['description'])) {
                  $a['description'] = $_POST['description'];
                }

                $data = Item::asignActivityBono($a);
                api_response_p($data);
            break;

            case 'getActivityName':
                $a = array();
                $a['id'] = $_GET['id'];

                $data = Item::getActivityName($a);
                api_response_p($data);
            break;

            case 'removeActivityBono':
                $a = array();
                $a['id'] = $_GET['id'];
                $a['name'] = $_GET['name'];

                $data = Item::removeActivityBono($a);
                api_response_p($data);
            break;

            //GESTION DE GASTOS
            case 'getTotalExpenses':
                $a = array();
                $a["date"]=$_GET["date"];
                $data = Transaction::getTotalExpenses($a);
                api_response_p($data);
            break;

            case 'getExpensesAll':
                $a = array();
                $data = Transaction::getExpensesAll();
                api_response_p($data);
            break;

            case 'delExpense':
                $a = array();
                $a['id'] = $_GET['idExpense'];
                $data = Transaction::delExpense($a);
                api_response_p($data);
            break;

            case 'addTransaction':
                $a = array();
                $a['fecha'] = $_GET['fecha'];                
                if(isset($_GET['tags'])){
                    $a['tags'] = $_GET['tags'];
                }else{
                    $a['tags'] = "";
                }
                if(isset($_GET['desc'])){
                    $a['desc'] = $_GET['desc'];
                }else{
                    $a['desc'] = "";
                }
                if(isset($_GET['account'])){
                    $a['account'] = $_GET['account'];
                }else{
                    $a['account'] = "account";
                }
                $a['cantidad'] = $_GET['cantidad'];
                $data = Transaction::addTransaction($a);
                api_response_p($data);
            break;

            //MY BOX

            case 'getConfig':
                $a = array();
                $data = myBox::getConfig($a);
                api_response_p($data);
            break;

            case 'editConfig':
                $a = array();
                $a["aforo"]=$_GET["aforo"];
                $a["horasPrev"]=$_GET["horasPrev"];
                $a["horasPrevUnlimited"]=$_GET["horasPrevUnlimited"];
                $a["MinHoras"]=$_GET["MinHoras"];
                $data = myBox::editConfig($a);
                api_response_p($data);
            break;

            case 'getTaxes':
                $a = array();
                $data = myBox::getTaxes();
                api_response_p($data);
            break;

            case 'getDatsMyBoxFacturation':
                $a = array();
                $data = myBox::getDatsMyBoxFacturation();
                api_response_p($data);
            break;

            case 'editFacturationBox':
                $a = array();
                $a["diaFactV"]=$_GET["diaFactV"];
                $a["dayBlockAccessV"]=$_GET["dayBlockAccessV"];
                $a["ivaSelectedV"]=$_GET["ivaSelectedV"];
                $data = myBox::editFacturationBox($a);
                api_response_p($data);
            break;

            //wait list
            case 'getWaitList':
                $a = array();
                $a["idReservation"]=$_GET["reservation_id"];
                $data = User::getWaitList($a);
                api_response_p($data);
            break;

            //add user
            case 'addAccount':
                $a = array();
                $a["email"]=$_GET["email"];
                $data = User::addAccount($a);
                api_response_p($data);
            break;

            //add invoices publicidad y seguro.
            case 'addInvoicesPubliSecure':
                $a = array();
                $a["email"]=$_GET["email"];
                $a["tAl"]=$_GET["tAl"];
                $a["curso"]=$_GET["curso"];
                $data = Invoice::addInvoicesPubliSecure($a);
                api_response_p($data);
            break;

            //add invoicesItem para facturación
            case 'addInvoicesItem':
                $a = array();
                $a["code"]=$_GET["code"];
                $a["idInvoice"]=$_GET["idInvoice"];
                $data = Invoice::addInvoicesItem($a);
                api_response_p($data);
            break;

            //add invoice ae>forvial
            case 'addInvoiceCourseAE':
                $a = array();
                $a['importe'] = $_GET['importe'];
                $a['email'] = $_GET['email'];
                $a['curso'] = $_GET['curso'];
                $a['profesional'] = $_GET['profesional'];
                $a['prefix'] = $_GET['prefix'];
                $a['invoicenum'] = $_GET['invoicenum'];
                if(isset($_GET['notes'])){
                    $a['notes'] = $_GET['notes'];
                }else{
                    $a['notes'] = "";
                }
                $data = Invoice::addInvoiceCourseAE($a);
                api_response_p($data);
            break;

            //add usuario admin ibilling AE
            case 'addUserEmployee':
                $a = array();
                $a['email'] = $_GET['email'];
                $a['nameAE'] = $_GET['nameAE'];
                $a['nombre'] = $_GET['nombre'];
                $data = User::addUserEmployee($a);
                api_response_p($data);
            break;

            //Cambiar estado de la factura.
            case 'changeStatusInvoice':
                $a = array();
                $a['id'] = $_GET['id'];
                $a['newStatus'] = $_GET['newStatus'];
                $data = Invoice::changeStatusInvoice($a);
                api_response_p($data);
            break;

            //Cambiar estado de la factura.
            case 'cloneIB':
                $a = array();
                $a['schoolname'] = $_GET['name'];
                $data = Invoice::cloneIBC($a);
                api_response_p($data);
            break;

            default:
                $data['msg'] = 'Method Not Found';
                api_response_p($data);
        }

    }
    else{
        $data['msg'] = 'Invalid API Key';
        api_response($data);
    }

}
else{

    $data['msg'] = 'API Key is r';

    api_response($data);

}
