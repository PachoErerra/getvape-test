<?php

use app\models\AssortmentTranslates;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Telegram\Bot\Api;

class ControllerToolGenerateUsersFile extends Controller
{

    public function index()
    {

        if (isset($this->request->get['token']) && $this->request->get['token'] == 'PfX54hQBZWfowQjhy4wt1MqpuIAFU') {

            $this->log->write("ЗАПУСК");

            $this->load->model('customer/customer');

            $this->load->model('setting/setting');

            $telegramUsers = $this->model_customer_customer->getTelegramUsers();

            if ($telegramUsers) {
                $this->actionGenerateUsersFile($telegramUsers);
            }
        }
    }


    private
    function actionGenerateUsersFile($telegramUsers)
    {
        $inputFilePath = DIR_DOWNLOAD . 'telegram-users.xlsx';


        $today = date("Y-m-d H:i:s");

        $today = new DateTime($today);


        unlink($inputFilePath);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $cntCell = 1;
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(150, 'pt');
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(100, 'pt');
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(100, 'pt');
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(150, 'pt');

        $sheet->setCellValue('A' . $cntCell, 'ФИО');
        $sheet->setCellValue('B' . $cntCell, 'Номер тел.');
        $sheet->setCellValue('C' . $cntCell, 'Telegram Username');
        $sheet->setCellValue('D' . $cntCell, 'Дата последнего заказа');
        $cntCell++;


        foreach ($telegramUsers as $telegramUser) {

            $lastOrderDate = '-';

            $color = '';

            $lastOrder = $this->model_customer_customer->getLastOrderByCustomerId($telegramUser['user_id']);

            if ($lastOrder) {

                $interval_day_sending_messages = $this->model_setting_setting->getSettingValue('interval_day_sending_messages');

                $lastOrderDate = '№' . $lastOrder['order_id'] . ' ' . date('d.m.Y', strtotime($lastOrder['date_added']));

                $date_added = new DateTime($lastOrder['date_added']);

                $interval = $today->diff($date_added);

                if ($interval->days > $interval_day_sending_messages) {
                    $color = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED;
                }

            } else {
                $color = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED;
            }


            if ($telegramUser['firstname'] || $telegramUser['lastname']) {
                $fullName = $telegramUser['firstname'] . ' ' . $telegramUser['lastname'];
            } else {
                $fullName = $telegramUser['first_name'] . ' ' . $telegramUser['last_name'];
            }

            $telephone = $telegramUser['telephone'];
            $telegramId = $telegramUser['username'];
            $sheet->setCellValue('A' . $cntCell, $fullName);
            $sheet->setCellValue('B' . $cntCell, $telephone);
            $sheet->setCellValue('C' . $cntCell, ($telegramId) ? '@' . $telegramId : '');
            $sheet->setCellValue('D' . $cntCell, $lastOrderDate);

            if ($color) {
                $spreadsheet
                    ->getActiveSheet()
                    ->getStyle('D' . $cntCell)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB($color);
            }
            $cntCell++;


        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($inputFilePath);

    }
}