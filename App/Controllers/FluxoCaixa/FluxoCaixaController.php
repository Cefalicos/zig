<?php
namespace App\Controllers\FluxoCaixa;

use App\Models\ProdutoCategoria;
use App\Rules\Logged;
use App\Services\UploadService\UploadFiles;
use Exception;
use System\Controller\Controller;
use System\Get\Get;
use System\Post\Post;
use System\Session\Session;
use App\Models\FluxoCaixa;

class FluxoCaixaController extends Controller
{
    protected $post;
    protected $get;
    protected $layout;
    protected $idEmpresa;
    protected $diretorioImagemProdutoNoEnv;
    protected $diretorioImagemProdutoPadrao;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'default';

        $this->post = new Post();
        $this->get = new Get();
        $this->idEmpresa = Session::get('idEmpresa');

        $logged = new Logged();
        $logged->isValid();
    }

    public function index()
    {
        $periodo = ['de' => date('Y/m/d'), 'ate' => date('Y/m/d')];

        $fluxoCaixa = new FluxoCaixa();
        $fluxo = $fluxoCaixa->fluxoDeCaixa($periodo, $this->idEmpresa);
        $fluxoDetalhadoPorMes = $fluxoCaixa->fluxoDeCaixaDetalhadoPorMes($periodo, $this->idEmpresa);



        $this->view('fluxoDeCaixa/index', $this->layout, compact('fluxo', 'fluxoDetalhadoPorMes'));
    }

    public function modalRegistrarMovimentacao()
    {
        $this->view('fluxoDeCaixa/formulario_movimentacao', null);
    }

    public function save()
    {
        $fluxoCaixa = new FluxoCaixa();
        $dados = (array) $this->post->data();
        $dados['id_empresa'] = $this->idEmpresa;
        $dados['data'] = date('Y-m-d H:i:s', strtotime($dados['data']));
        $dados['valor'] = formataValorMoedaParaGravacao($dados['valor']);
        $dados['tipo_movimento'] = (is_null($dados['tipo_movimento'])) ? 0 : $dados['tipo_movimento'];

        try {
            $fluxoCaixa->save($dados);
            return $this->get->redirectTo("fluxoDeCaixa/index");

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
