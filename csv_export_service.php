<?php

class  CsvExportService {

        /**
     * Export to CSV file
     * @param array $header
     * @param array $data
     * @param string $fileName
     */
    public function exportToCSV($header, $data, $fileName) {
        ob_start();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        ob_end_clean();

        $output = fopen( 'php://output', 'w' );

        fputcsv( $output, $header);

        foreach( $data as $key => $value){
            fputcsv($output, $value);
        }

        fclose($output);
        exit;
    } 

        /**
     * Creates file name
     * @param string $name
     * @return string
     */
    public function createFilename($name) {
        $nameConverted = preg_replace('/[^a-zA-Z0-9]/', '_', $name);

        $currentDateTime = date('Y_m_d-H_i');
        $filename = $nameConverted . '_' . $currentDateTime . '.csv';

        return $filename;
    }
}
?>