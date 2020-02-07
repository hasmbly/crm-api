<?php
// use App\Helpers\makePdf;
// use PDF;
// use App\Mail\MailtrapExample;
// use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB; 
use Illuminate\Database\QueryException;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/	
date_default_timezone_set('Asia/Jakarta');

$router->get('/', function () use ($router) {

	return  $router->app->welcome();
	// 
	// return view('myPdf');
});
		

// $router->get('/set', function () use ($router) {
// 	$data = [];
// 	try {
// 		$q = DB::table('tbl_pelayanan')->select('id_pelayanan')
// 		->get();
// 		foreach ($q as $key) {
// 			$tiket = 'PL00'.$key->id_pelayanan;
// 			$q = DB::table('tbl_pelayanan')
// 			->where('id_pelayanan', $key->id_pelayanan)
// 			->update(array(
// 				'no_pelayanan' => $tiket,
// 			));
// 			// array_push($data, $tiket);
// 		}			
// 	} catch (QueryException $e) {
//         return response()->json([
//             'error'       => true,
//             'code'        => $e->getCode(),
//             'message'     => $e->getMessage()
//           ], 500);
// 	}
//           return response()->json([
//             'error'   => false,
//             'msg'     => "Success"
//           ], 200);		
// });

$router->group(['prefix'=>'api/v1'], function() use($router){

	$router->get('bcrypt/{pass}', function( $pass ) {
	        return response()->json(password_hash($pass, PASSWORD_BCRYPT));
    	});
	   
	/**
	 * Nik Routes
	 */
	$router->get('nik/{nik}', 'Nik\SearchNIKController@show');

	/**
	 * lookup
	 */
		
    // Show Chart Front     
    $router->get('lookup/chart/front', 'Chart\ChartFrontController@show');  
	
	// Show All Instansi
	$router->get('lookup/instansi', 'UserAdmin\InstansiController@fetch');

	// Show ALl tema
	$router->get('lookup/tema', 'UserAdmin\JenisPertanyaanController@fetch');
	
	// show All pertayaan
	$router->get('lookup/pertanyaan', 'UserAdmin\PertanyaanController@fetch');

	// show All list pertanyaan
	$router->get('lookup/list-pelayanan', 'UserAdmin\ListPelayananController@fetch');

	// show All Info-grafis
	$router->get('lookup/info-grafis', 'Informasi\InfoGrafisController@fetch');	
	// show All FAQ
	$router->get('lookup/faq', 'Informasi\FaqController@fetch');			
		
	// show All Antrian
	$router->get('lookup/antrian', 'Pengaduan\PengaduanController@fetchAntrian');	

	/**
	 * Pengaduan Routes
	 */

	// Pengaduan Create New Pengaduan+antrian+iAsk from Citizen
	$router->post('pengaduan/add', 'Pengaduan\PengaduanController@create');

	// Pengaduan Create New Pengaduan+antrian+iAsk from Citizen
	$router->get('pengaduan/download/ticket', 'Pengaduan\PengaduanController@getPdf');	

	// Pengaduan Create Comment Pengaduan+antrian+iAsk from Citizen
	$router->post('pengaduan/comment/add', 'Pengaduan\PengaduanController@addComment');

	// Pengaduan Show and search with Historical by Nik or Ticket
	$router->get('pengaduan', 'Pengaduan\PengaduanController@show');		

	/**
	 * iAsk mcrypt_module_self_test(algorithm)
	 */

	// iAsk Create New Pengaduan+antrian+iAsk from Citizen
	$router->post('iask/add', 'iAsk\iAskController@create');

	// iAsk Create Comment Pengaduan+antrian+iAsk
	$router->post('iask/comment/add', 'iAsk\iAskController@addComment');	

	// Pengaduan Show Historical iAsk + Comment from Citizen by Nik or Ticket
	$router->get('iask', 'iAsk\iAskController@show');		


	/**
	 * Pelayanan Routes
	 */

	// pelayanan Create New
	$router->post('pelayanan/add', 'Pelayanan\PelayananController@create');

	// pelayanan dowload sample
	$router->get('pelayanan/download/sample', 'Pelayanan\PelayananController@sampleDownload');

	/**
	 * Auth Super Admin Login
	 */

	// login
	$router->post('auth/signin', 'AuthController@authenticate');

	$router->post('auth/forget', 'AuthController@resetPassByEmail');	

});

	/**
	 *  begin::Admin Behavior
	 */

$router->group(['middleware' => 'admin.auth'], function() use ($router) {

			$router->group(['prefix'=>'api/v1'], function() use($router){		

			/**
			 * Admin
			 */

			// Edit Admin
			// $router->post('users/admin/profile', 'UsersAdmin\AdminController@updateAdminProfile');

			// // show all Operator
			// $router->get('users/admin/operator', 'UsersAdmin\AdminController@fetchOperator');			

			// // Add Operator
			// $router->post('users/admin/operator', 'UsersAdmin\AdminController@addOperator');

			// // show by id Operator
			// $router->post('users/admin/operator/{id}', 'UsersAdmin\AdminController@addOperator');			

	/*INSTANSI*/

	// Show All Instansi with paginate
	$router->post('instansi', 'UserAdmin\InstansiController@index');	

	// Create Instansi
	$router->post('instansi/add', 'UserAdmin\InstansiController@create');

	// Update Instansi
	$router->put('instansi', 'UserAdmin\InstansiController@update');

	// Show Instansi by Id
	$router->get('instansi', 'UserAdmin\InstansiController@show');	

	// Delete Instansi
	$router->post('instansi/{id}', 'UserAdmin\InstansiController@destroy');

	/*TEMA PERTANYAAN*/

	// Show ALl tema with paginate
	$router->post('tema', 'UserAdmin\JenisPertanyaanController@index');

	// Create Jenis pertanyaan
	$router->post('tema/add', 'UserAdmin\JenisPertanyaanController@create');

	// Update Jenis pertanyaan
	$router->put('tema', 'UserAdmin\JenisPertanyaanController@update');

	// Show Jenis pertanyaan by Id
	$router->get('tema', 'UserAdmin\JenisPertanyaanController@show');		

	// Delete Jenis pertanyaan
	$router->post('tema/{id}', 'UserAdmin\JenisPertanyaanController@destroy');	

	/*PERTANYAAN*/

	// show All pertanyaan with paginate
	$router->post('pertanyaan', 'UserAdmin\PertanyaanController@index');

	// Create Pertanyaan
	$router->post('pertanyaan/add', 'UserAdmin\PertanyaanController@create');

	// Update Pertanyaan
	$router->put('pertanyaan', 'UserAdmin\PertanyaanController@update');

	// show Pertanyaan by id
	$router->get('pertanyaan', 'UserAdmin\PertanyaanController@show');	

	// Delete Pertanyaan
	$router->post('pertanyaan/{id}', 'UserAdmin\PertanyaanController@destroy');


	/**
	 * Users Controller
	 */

	// users Show All with Paginate using Helpers : ifSuperAdmin
	$router->post('users', 'UserAdmin\UsersController@index');	

	// users Show All with Paginate
	$router->post('users/add', 'UserAdmin\UsersController@create');		

	// users delete by id
	$router->post('users/{id}', 'UserAdmin\UsersController@destroy');


	/**
	 * Roles Controller
	 */

	// users lookup roles using Helpers : ifSuperAdmin
	$router->get('lookup/roles', 'UserAdmin\RolesController@fetch');		

	/**
	 *  end::Admin Behavior
	 */

/*
   $router->get('users', function() {
   
	   $users = \App\User::all();
	        return response()->json($users);
    password_hash('pusd4t1n', PASSWORD_BCRYPT)
    	});
*/

	});
	
});

	/**
	 *  begin::Operator Behavior
	 */

$router->group(['middleware' => 'operator.auth'], function() use ($router) {

			$router->group(['prefix'=>'api/v1'], function() use($router){	

	// Show Chart Back
	$router->get('lookup/chart', 'Chart\ChartController@show');

	// Show Ticket Status
	$router->get('lookup/ticket/{status}', 'Chart\PengaduanController@showTicketByStatus');	

	/**
	 * {pengaduan}
	 */

	// Pengaduan Show All pengaduan order by lastest addd_at
	$router->post('pengaduan', 'Pengaduan\PengaduanController@index');

	// Pengaduan updatePengaduan
	$router->put('pengaduan', 'Pengaduan\PengaduanController@update');

	// Pengaduan delete Pengaduan
	$router->post('pengaduan/{ticket}', 'Pengaduan\PengaduanController@destroy');

	/**
	 * iAsk
	 */
	
	// Pengaduan Show All iAsk order by lastest addd_at
	$router->post('iask', 'iAsk\iAskController@index');

	// Pengaduan update Pengaduan
	$router->put('iask', 'iAsk\iAskController@update');

	// Pengaduan delete Pengaduan
	$router->post('iask/{ticket}', 'iAsk\iAskController@destroy');


	/**
	 * Pelayanan
	 */
	
	// pelayanan Show All with Paginate
	$router->post('pelayanan', 'Pelayanan\PelayananController@index');	

	// pelayanan Show id + dowload sample / download uploaded images
	$router->get('pelayanan', 'Pelayanan\PelayananController@show');	
	
	// update pelayanan
	$router->put('pelayanan', 'Pelayanan\PelayananController@update');	

	// pelayanan download file
	$router->get('pelayanan/download/{id}', 'Pelayanan\PelayananController@downloadFile');	

	// pelayanan delete by id
	$router->post('pelayanan/{id}', 'Pelayanan\PelayananController@destroy');

			/* list_pelayanan */

			// Create Pertanyaan
			$router->post('list-pelayanan/add', 'UserAdmin\ListPelayananController@create');

			// Update Pertanyaan
			$router->put('list-pelayanan', 'UserAdmin\ListPelayananController@update');

			// show Pertanyaan by id
			$router->get('list-pelayanan', 'UserAdmin\ListPelayananController@show');	

			// Delete Pertanyaan
			$router->post('list-pelayanan/{id}', 'UserAdmin\ListPelayananController@destroy');	
	
	/*  Info-Grafis */

	// info-grafis Show All with Paginate
	$router->post('info-grafis', 'Informasi\InfoGrafisController@index');
	
	// info-grafis Create New
	$router->post('info-grafis/add', 'Informasi\InfoGrafisController@create');

	// update info-grafis
	$router->put('info-grafis', 'Informasi\InfoGrafisController@update');	

	// info-grafis Show by Id
	$router->get('info-grafis', 'Informasi\InfoGrafisController@show');

	// info-grafis delete by id
	$router->post('info-grafis/{id}', 'Informasi\InfoGrafisController@destroy');

	/*  FAQ */

	// faq Show All with Paginate
	$router->post('faq', 'Informasi\FaqController@index');

	// faq Create New
	$router->post('faq/add', 'Informasi\FaqController@create');
	
	// faq Show id
	$router->get('faq', 'Informasi\FaqController@show');	

	// update faq
	$router->put('faq', 'Informasi\FaqController@update');	

	// faq delete by id
	$router->post('faq/{id}', 'Informasi\FaqController@destroy');		

	/*  chart */

	// chart Show All with Paginate
	$router->post('chart', 'Informasi\ChartController@show');	
	
	// update chart
	$router->put('chart', 'Informasi\ChartController@update');	

	// chart Show by Id
	$router->get('chart', 'Informasi\ChartController@show');

	// chart delete by id
	$router->post('chart/{id}', 'Informasi\ChartController@destroy');	

	/**
	 * User
	 */
	// update users
	$router->put('users', 'UserAdmin\UsersController@update');	

	// users Show by Id
	$router->get('users', 'UserAdmin\UsersController@show');

	// users reset pass by id
	$router->get('users/reset-pass', 'UserAdmin\UsersController@resetPass');		

	/**
	 *  end::Operator Behavior
	 */	
	
	});
	
});

	$router->group(['middleware' => 'super_admin.auth'], function() use ($router) {

	});
