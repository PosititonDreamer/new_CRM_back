<?php
require_once __DIR__ . '/../libraries/FPDF/fpdf.php';
require_once __DIR__ . '/../libraries/FPDI/src/autoload.php';

use setasign\Fpdi\Fpdi;

class PDF_Rotate extends \setasign\Fpdi\Fpdi
{
    protected $angle = 0;

    /**
     * Поворот системы координат.
     * @param float $angle Угол в градусах (против часовой стрелки)
     * @param float $x     Абсцисса центра вращения
     * @param float $y     Ордината центра вращения
     */
    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1) $x = $this->x;
        if ($y == -1) $y = $this->y;
        if ($this->angle != 0) $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf(
                'q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',
                $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy
            ));
        }
    }

    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
}

function transformBlank($order_id) {
    $sourceFile = __DIR__ . "/../../files/$order_id.pdf";
    if(file_exists($sourceFile)) {
        $a5Width  = 148;
        $a5Height = 210;

        $pdf = new PDF_Rotate();
        $pdf->setSourceFile($sourceFile);
        $tplId = $pdf->importPage(1, 'MediaBox');

        // Размеры исходной страницы (A5 портрет: ~148×210)
        $size = $pdf->getTemplateSize($tplId);
        $Wt = $size['width'];
        $Ht = $size['height'];

        // Новая страница — A5 в АЛЬБОМНОЙ ориентации: 210×148
        $pdf->AddPage('L', [$a5Height, $a5Width]);

        // Масштаб, чтобы повёрнутый на 90° контент влез в альбомный A5.
        // После поворота ширина контента = Ht, высота = Wt.
        $scale = min($a5Height / $Ht, $a5Width / $Wt);
        $w = $Wt * $scale;   // ширина шаблона в исходной ориентации
        $h = $Ht * $scale;   // высота шаблона в исходной ориентации

        // Размеры повёрнутого контента на странице
        $Wr = $h;            // ширина на странице = высота шаблона
        $Hr = $w;            // высота на странице = ширина шаблона

        // Центрируем на странице
        $X = ($a5Height - $Wr) / 2;
        $Y = ($a5Width - $Hr) / 2;

        // Координаты левого верхнего угла шаблона в повёрнутой системе
        $x = -$Y - $h + 62;
        $y = $X;

        // Поворот содержимого на 90° вокруг левого верхнего угла страницы
        $pdf->Rotate(90, 0, 0);
        $pdf->useTemplate($tplId, $x, $y, $w, $h);
        $pdf->Rotate(0);

        $pdf->Output('F', $sourceFile);

        $pdf = new Fpdi();

        $pdf->setSourceFile($sourceFile);
        $tplId = $pdf->importPage(1,'MediaBox');

        $pdf->AddPage('P', 'A4');

        $pdf->useTemplate($tplId);
        $pdf->Output('F', $sourceFile);
    }
}


