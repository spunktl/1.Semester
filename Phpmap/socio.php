<?php
/**
 * Created by PhpStorm.
 * User: georgina scholes
 * Date: 7/12/2016
 * Time: 9:33 PM
 */
/**
 * PostGIS to GeoJSON
 * Query a PostGIS table or view and return the results in GeoJSON format, suitable for use in OpenLayers, Leaflet, etc.
 *
 * @param 		string		$geotable		The PostGIS layer name *REQUIRED*
 * @param 		string		$geomfield		The PostGIS geometry field *REQUIRED*
 * @param 		string		$srid			The SRID of the returned GeoJSON *OPTIONAL (If omitted, EPSG: 4326 will be used)*
 * @param 		string 		$fields 		Fields to be returned *OPTIONAL (If omitted, all fields will be returned)* NOTE- Uppercase field names should be wrapped in double quotes
 * @param 		string		$parameters		SQL WHERE clause parameters *OPTIONAL*
 * @param 		string		$orderby		SQL ORDER BY constraint *OPTIONAL*
 * @param 		string		$sort			SQL ORDER BY sort order (ASC or DESC) *OPTIONAL*
 * @param 		string		$limit			Limit number of results returned *OPTIONAL*
 * @param 		string		$offset			Offset used in conjunction with limit *OPTIONAL*
 * @return 		string					resulting geojson string
 */
function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
    $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
    $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
    $result = str_replace($escapers, $replacements, $value);
    return $result;
}


# Connect to PostgreSQL database
$conn = pg_connect("dbname='semester1' user='postgres' password='postgres' host='localhost'");
if (!$conn) {
    echo "Not connected : " . pg_error();
    exit;
}

# Build SQL SELECT statement and return the geometry as a GeoJSON element in EPSG: 4326
$sql = "SELECT alder_pct_, alder_pct0, alder_pct1, alder_pct2, alder_pct3, alder_pct4, alder_pct5 FROM socio_data WHERE rode_nr = " . $_GET['rode_nr'];


//echo $sql;

# Try query or error
$rs = pg_query($conn, $sql);
if (!$rs) {
    echo "An SQL error occured.\n";
    exit;
}

# Build GeoJSON
$dataOutput = '';
$labelArray = array(
    "0-5", "6-17", "18-29", "30-39", "40-49", "50-64", "65+"
);
while ($row = pg_fetch_assoc($rs)) {
    $i = 0;
    foreach ($row as $key => $val) {
        # example line { y: 9.21, label: "0-5" },

        $dataOutput .= '{ "y": ' . escapeJsonString($val) . ', "label": "' . $labelArray[$i] . '"}';
        if ($i != 6){
            $dataOutput .= ',';
        }
        $i ++;
    }
}



$output = '{"data":[ ' . $dataOutput . ' ]}';
echo $output;
?>