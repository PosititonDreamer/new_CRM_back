<?php
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../libraries/FPDF/fpdf.php';
require_once __DIR__ . '/../libraries/FPDI/src/autoload.php';

use setasign\Fpdi\Fpdi;

$new_orders = [];

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $order = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`number`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id` = $id");
    $new_orders[] = mysqli_fetch_assoc($order);

} else {
    $status = $_GET["status"];

    if($status == 6) {
        $orders = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`number`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE (`orders`.`id_order_status` = 6 OR `orders`.`id_order_status` = 7) AND `clients_address`.`delivery` = 'Почта России' ORDER BY `orders`.`number`");

        while($order = mysqli_fetch_assoc($orders)){
            $new_orders[] = $order;
        }

        $orders = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`number`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE (`orders`.`id_order_status` = 6 OR `orders`.`id_order_status` = 7) AND `clients_address`.`delivery` = 'Яндекс Доставка'");

        while($order = mysqli_fetch_assoc($orders)){
            $new_orders[] = $order;
        }

        $orders = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`number`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE (`orders`.`id_order_status` = 6 OR `orders`.`id_order_status` = 7) AND `clients_address`.`delivery` = 'CDEK'");

        while($order = mysqli_fetch_assoc($orders)){
            $new_orders[] = $order;
        }
    } else {
        $orders = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`number`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_order_status` = $status AND `clients_address`.`delivery` = 'Почта России' ORDER BY `orders`.`number`");

        while($order = mysqli_fetch_assoc($orders)){
            $new_orders[] = $order;
        }

        $orders = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`number`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_order_status` = $status AND `clients_address`.`delivery` = 'Яндекс Доставка'");

        while($order = mysqli_fetch_assoc($orders)){
            $new_orders[] = $order;
        }

        $orders = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`number`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_order_status` = $status AND `clients_address`.`delivery` = 'CDEK'");

        while($order = mysqli_fetch_assoc($orders)){
            $new_orders[] = $order;
        }
    }
}

$pdf = new Fpdi();

$pageWidth  = 148; // мм A4
$pageHeight = 210; // мм A4
$number_page = 2;

for ($i = 0; $i < count($new_orders); $i += 2) {
    if($new_orders[$i]['delivery'] == 'CDEK') {
        if(!file_exists(__DIR__ .'/../../files/' . $new_orders[$i]['id'] . '.pdf')) {
            $i--;
            continue;
        }

        if($number_page == 2) {
            $pdf->AddPage('L', 'A5');
            $number_page = 0;
        }
        $dashLength = 5; // длина штриха
        $gapLength  = 3; // длина пробела

        $y1 = 0;
        $y2 = $pageWidth;
        $x  = $pageHeight / 2; // по центру страницы

        // Рисуем горизонтальную пунктирную линию
        $currentY = $y1;
        while ($currentY < $y2) {
            $yEnd = min($currentY + $dashLength, $y2);
            $pdf->Line($x, $currentY, $x, $yEnd);
            $currentY += $dashLength + $gapLength;
        }
        // ----- Верхняя половина первого файла -----
        $pdf->setSourceFile(__DIR__ .'/../../files/' . $new_orders[$i]['id'] . '.pdf');
        $tplIdx = $pdf->importPage(1, 'MediaBox');
        $size = $pdf->getTemplateSize($tplIdx);

        $pdf->useTemplate(
            $tplIdx,
            $number_page == 0 ? 0: $pageHeight / 2,
            0,
            $size['width'],
            $size['height'],
            false,
        );
        $number_page++;

        if(!isset($new_orders[$i+1])) {
            break;
        }

        if( !file_exists(__DIR__ .'/../../files/' . $new_orders[$i + 1]['id'] . '.pdf')) {
            $i--;
            continue;
        }

        if($number_page == 2) {
            $pdf->AddPage('L', 'A5');
            $number_page = 0;
        }

        $pdf->setSourceFile(__DIR__ .'/../../files/' . $new_orders[$i + 1]['id'] . '.pdf');
        $tplIdx = $pdf->importPage(1, 'MediaBox');
        $size = $pdf->getTemplateSize($tplIdx);

        $pdf->useTemplate(
            $tplIdx,
            $number_page == 0 ? 0: $pageHeight / 2,
            0,
            $size['width'],
            $size['height'],
            false,
        );
        $number_page++;

    } else {

        if(!file_exists(__DIR__ .'/../../files/' . $new_orders[$i]['id'] . '.pdf')) {
            $i--;
            continue;
        }

        if(is_null($new_orders[$i]['number']) && $new_orders[$i]['delivery'] == 'Почта России') {
            $pdf->AddPage('L', 'A5');
            $pdf->setSourceFile(__DIR__ .'/../../files/' . $new_orders[$i]['id'] . '.pdf');
            $tplIdx = $pdf->importPage(1);
            $size = $pdf->getTemplateSize($tplIdx);
            $pdf->useTemplate($tplIdx,3,1,$pageHeight,$pageWidth,false);

            $i--;

            continue;
        }
        if($number_page == 2) {
            $pdf->AddPage('P', 'A5');
            $number_page = 0;
            $dashLength = 5; // длина штриха
            $gapLength  = 3; // длина пробела

            $x1 = 0;
            $x2 = $pageWidth;
            $y  = $pageHeight / 2; // по центру страницы

            // Рисуем горизонтальную пунктирную линию
            $currentX = $x1;
            while ($currentX < $x2) {
                $xEnd = min($currentX + $dashLength, $x2);
                $pdf->Line($currentX, $y, $xEnd, $y);
                $currentX += $dashLength + $gapLength;
            }
        }


        // ----- Верхняя половина первого файла -----
        $pdf->setSourceFile(__DIR__ .'/../../files/' . $new_orders[$i]['id'] . '.pdf');
        $tplIdx = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplIdx);

        $pdf->useTemplate(
            $tplIdx,
            0,
            $number_page == 0 ? 0: $pageHeight / 2,
            $pageWidth,
            $pageHeight,
            false,
        );
        $number_page++;

        if(!isset($new_orders[$i+1])) {
            break;
        }

        if( !file_exists(__DIR__ .'/../../files/' . $new_orders[$i + 1]['id'] . '.pdf')) {
            $i--;
            continue;
        }

        if($new_orders[$i + 1]['delivery'] == 'CDEK') {
            $number_page = 2;
            $i--;
            continue;
        }

        if($number_page == 2) {
            $pdf->AddPage('P', 'A5');
            $number_page = 0;
            $dashLength = 5; // длина штриха
            $gapLength  = 3; // длина пробела

            $x1 = 0;
            $x2 = $pageWidth;
            $y  = $pageHeight / 2; // по центру страницы

            // Рисуем горизонтальную пунктирную линию
            $currentX = $x1;
            while ($currentX < $x2) {
                $xEnd = min($currentX + $dashLength, $x2);
                $pdf->Line($currentX, $y, $xEnd, $y);
                $currentX += $dashLength + $gapLength;
            }
        }

        $pdf->setSourceFile(__DIR__ .'/../../files/' . $new_orders[$i + 1]['id'] . '.pdf');
        $tplIdx = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplIdx);

        $pdf->useTemplate(
            $tplIdx,
            0,
            $number_page == 0 ? 0: $pageHeight / 2,
            $pageWidth,
            $pageHeight,
            false,
        );
        $number_page++;
    }
}

//echo "<pre>";
//print_r($new_orders);
//echo "</pre>";

// Сохраняем объединённый PDF
$pdf->Output("I", "merged.pdf");



