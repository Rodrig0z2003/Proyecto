<?php

namespace App\Http\Controllers;

use App\Models\Pasajero;
use App\Models\Vuelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        if (auth()->check()) {
            return view('welcome');
        }
        return redirect('/login');
    }

    public function verListaVuelos() {
        $vuelos = Vuelo::paginate(20);

        return view('lista-vuelos', [
            'vuelos' => $vuelos
        ]);
    }

    public function guardarVuelo(Request $request) {
        $datos = $request->validate([
            'origen' => 'required|string',
            'destino' => 'required|string',
            'fecha_vuelo' => 'required|date',
            'hora_vuelo' => 'required|date_format:H:i',
            'precio_vuelo' => 'required|numeric',
            'cantidad_pasajeros' => 'required|numeric'
        ]);

        $vuelo = Vuelo::create($datos);
        $vuelo->save();
        return redirect('/lista-vuelos');
    }

    public function editarVuelo() {
        $vuelo = Vuelo::find(request()->route('id'));
        return view('editar-vuelo', [
            'vuelo' => $vuelo
        ]);
    }

    public function actualizarVuelo(Request $request) {
        $datos = $request->validate([
            'origen' => 'required|string',
            'destino' => 'required|string',
            'fecha_vuelo' => 'required|date',
            'hora_vuelo' => 'required|date_format:H:i',
            'precio_vuelo' => 'required|numeric',
            'cantidad_pasajeros' => 'required|numeric'
        ]);

        $vuelo = Vuelo::find(request()->route('id'));
        $vuelo->update($datos);
        $vuelo->save();
        return redirect('/lista-vuelos');
    }

    public function eliminarVuelo(Request $request) {
        $vuelo = Vuelo::find($request->route('id'));
        $vuelo->delete();
        return redirect()->route('lista-vuelos');
    }

    public function agregarPasajeros() {
        $vuelo = Vuelo::find(request()->route('id'));
        $pasajeros = Pasajero::where('vuelo_id', $vuelo->id)->get();

        return view('agregar-pasajeros', [
            'vuelo' => $vuelo,
            'pasajeros' => $pasajeros
        ]);
    }

    public function guardarPasajeros(Request $request) {
        $datos = $request->validate([
            'nombre' => 'required|string',
            'apellido' => 'required|string',
            'celular' => 'required|string',
            'numero_asientos' => 'required|numeric'
        ]);

        $vuelo = Vuelo::find(request()->route('id'));
        $pasajero = new Pasajero($datos);
        $pasajero->vuelo()->associate($vuelo);
        $pasajero->save();

        // return redirect('/lista-vuelos');
        return redirect()->route('agregar-pasajeros', ['id' => $vuelo->id]);
    }
    public function eliminarPasajero(Request $request) {
        $pasajero = Pasajero::find($request->route('id'));
        $pasajero->delete();
        return redirect()->route('agregar-pasajeros', ['id' => $pasajero->vuelo_id]);
    }

    public function enviarMensaje(Request $request) {
        $pasajero_id = $request->route('id');
        $pasajero = Pasajero::find($pasajero_id);
        $celular = $pasajero->celular;

        // Replace the placeholder values with your actual GreenApi credentials and message content
        $url = 'https://api.green-api.com/waInstance7103865763/SendMessage/03d85c1ff2764af1b0fb9cb43dd2f4036c354d4f683340c69d';
        $data = [
            "chatId" => "51" . $celular . "@c.us",
            "message" => "Estimado(a) *" . $pasajero->nombre . " " . $pasajero->apellido . "*, gracias por viajar con nosotros"
            . "\nSu número de asientos comprados es: *" . $pasajero->numero_asientos . "*" . "\nSu vuelo es: *" . $pasajero->vuelo->origen . " - " . $pasajero->vuelo->destino . "*"
            . "\nFecha: *" . $pasajero->vuelo->fecha_vuelo . "*"
        ];

        $options = array(
            'http' => array(
                'method' => 'POST',
                'content' => json_encode($data),
                'header' => "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result);
        return redirect()->route('agregar-pasajeros', ['id' => $pasajero->vuelo_id]);
    }

}
