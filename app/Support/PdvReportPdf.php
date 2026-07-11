<?php
namespace App\Support;

require_once __DIR__ . '/fpdf/fpdf.php';

class PdvReportPdf extends \FPDF {
    private string $nomeEmpresa;
    private string $dataCaixa;

    public function __construct(string $nomeEmpresa, string $dataCaixa) {
        // Portrait, mm, A4
        parent::__construct('P', 'mm', 'A4');
        $this->nomeEmpresa = $nomeEmpresa;
        $this->dataCaixa = $dataCaixa;
        $this->SetAutoPageBreak(true, 15);
        $this->SetMargins(10, 10, 10);
    }

    public function toLatin1(string $str): string {
        $val = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $str);
        if ($val === false) {
            return utf8_decode($str);
        }
        return $val;
    }

    public function Header() {
        // Logo
        $logoPath = 'c:/xampp/htdocs/sistema_conectados/public/assets/img/logo-print.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 10, 45);
        } else {
            $this->SetFont('Arial', 'B', 16);
            $this->SetTextColor(0, 52, 154);
            $this->Text(10, 18, $this->toLatin1($this->nomeEmpresa));
        }

        // Título e Subtítulo
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(0, 52, 154); // Brand Blue
        $this->Cell(0, 8, $this->toLatin1('RELATÓRIO FINANCEIRO'), 0, 1, 'R');
        
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 116, 139); // Muted
        $this->Cell(0, 5, $this->toLatin1('Resumo gerencial para acompanhamento de caixa'), 0, 1, 'R');
        $this->Ln(3);

        // Linha Divisora Grossa Azul (10mm a 200mm)
        $this->SetDrawColor(0, 52, 154);
        $this->SetLineWidth(0.8);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 116, 139);
        
        // Linha Divisora Fina Cinza (10mm a 200mm)
        $this->SetDrawColor(215, 222, 232);
        $this->SetLineWidth(0.2);
        $this->Line(10, $this->GetY() - 2, 200, $this->GetY() - 2);
        
        $this->Cell(95, 10, $this->toLatin1($this->nomeEmpresa), 0, 0, 'L');
        $this->Cell(95, 10, $this->toLatin1('Relatório gerado em ' . date('d/m/Y H:i:s') . '   |   Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }
}
