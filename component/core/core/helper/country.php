<?php

class Helper_Core_Country extends OSC_Object {

    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 0;

    protected $_countries = ['XK' => 'Kosovo', 'CW' => 'Curacao', 'BQ' => 'Bonaire, Saint-Eustache et Saba', 'SS' => 'South Sudan', 'SX' => 'Sint Maarten', 'AQ' => 'Antarctica', 'AS' => 'American Samoa', 'FM' => 'Micronesia', 'GU' => 'Guam', 'MH' => 'Marshall Islands', 'MP' => 'Northern Mariana Islands', 'PW' => 'Palau', 'VI' => 'U.S. Virgin Islands', 'AD' => 'Andorra', 'AE' => 'United Arab Emirates', 'AF' => 'Afghanistan', 'AG' => 'Antigua And Barbuda', 'AI' => 'Anguilla', 'AL' => 'Albania', 'AM' => 'Armenia', 'AN' => 'Netherlands Antilles', 'AO' => 'Angola', 'AR' => 'Argentina', 'AT' => 'Austria', 'AU' => 'Australia', 'AW' => 'Aruba', 'AX' => 'Aland Islands', 'AZ' => 'Azerbaijan', 'BA' => 'Bosnia And Herzegovina', 'BB' => 'Barbados', 'BD' => 'Bangladesh', 'BE' => 'Belgium', 'BF' => 'Burkina Faso', 'BG' => 'Bulgaria', 'BH' => 'Bahrain', 'BI' => 'Burundi', 'BJ' => 'Benin', 'BL' => 'Saint Barth&eacute;lemy', 'BM' => 'Bermuda', 'BN' => 'Brunei', 'BO' => 'Bolivia', 'BR' => 'Brazil', 'BS' => 'Bahamas', 'BT' => 'Bhutan', 'BV' => 'Bouvet Island', 'BW' => 'Botswana', 'BY' => 'Belarus', 'BZ' => 'Belize', 'CA' => 'Canada', 'CC' => 'Cocos (Keeling) Islands', 'CD' => 'Congo, The Democratic Republic Of The', 'CF' => 'Central African Republic', 'CG' => 'Congo', 'CH' => 'Switzerland', 'CI' => 'Côte d\'Ivoire', 'CK' => 'Cook Islands', 'CL' => 'Chile', 'CM' => 'Cameroon', 'CN' => 'China', 'CO' => 'Colombia', 'CR' => 'Costa Rica', //        'CU' => 'Cuba',
        'CV' => 'Cape Verde', 'CX' => 'Christmas Island', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DE' => 'Germany', 'DJ' => 'Djibouti', 'DK' => 'Denmark', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'DZ' => 'Algeria', 'EC' => 'Ecuador', 'EE' => 'Estonia', 'EG' => 'Egypt', 'EH' => 'Western Sahara', 'ER' => 'Eritrea', 'ES' => 'Spain', 'ET' => 'Ethiopia', 'FI' => 'Finland', 'FJ' => 'Fiji', 'FK' => 'Falkland Islands (Malvinas)', 'FO' => 'Faroe Islands', 'FR' => 'France', 'GA' => 'Gabon', 'GB' => 'United Kingdom', 'GD' => 'Grenada', 'GE' => 'Georgia', 'GF' => 'French Guiana', 'GG' => 'Guernsey', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GL' => 'Greenland', 'GM' => 'Gambia', 'GN' => 'Guinea', 'GP' => 'Guadeloupe', 'GQ' => 'Equatorial Guinea', 'GR' => 'Greece', 'GS' => 'South Georgia And The South Sandwich Islands', 'GT' => 'Guatemala', 'GW' => 'Guinea Bissau', 'GY' => 'Guyana', 'HK' => 'Hong Kong', 'HM' => 'Heard Island And Mcdonald Islands', 'HN' => 'Honduras', 'HR' => 'Croatia', 'HT' => 'Haiti', 'HU' => 'Hungary', 'ID' => 'Indonesia', 'IE' => 'Ireland', 'IL' => 'Israel', 'IM' => 'Isle Of Man', 'IN' => 'India', 'IO' => 'British Indian Ocean Territory', 'IQ' => 'Iraq', 'IR' => 'Iran', 'IS' => 'Iceland', 'IT' => 'Italy', 'JE' => 'Jersey', 'JM' => 'Jamaica', 'JO' => 'Jordan', 'JP' => 'Japan', 'KE' => 'Kenya', 'KG' => 'Kyrgyzstan', 'KH' => 'Cambodia', 'KI' => 'Kiribati', 'KM' => 'Comoros', 'KN' => 'Saint Kitts And Nevis', //        'KP' => 'Korea, Democratic People\'s Republic Of',
        'KR' => 'South Korea', 'KW' => 'Kuwait', 'KY' => 'Cayman Islands', 'KZ' => 'Kazakhstan', 'LA' => 'Laos', 'LB' => 'Lebanon', 'LC' => 'Saint Lucia', 'LI' => 'Liechtenstein', 'LK' => 'Sri Lanka', 'LR' => 'Liberia', 'LS' => 'Lesotho', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'LV' => 'Latvia', 'LY' => 'Libya', 'MA' => 'Morocco', 'MC' => 'Monaco', 'MD' => 'Moldova', 'ME' => 'Montenegro', 'MF' => 'Saint Martin', 'MG' => 'Madagascar', 'MK' => 'Macedonia', 'ML' => 'Mali', 'MM' => 'Myanmar', 'MN' => 'Mongolia', 'MO' => 'Macao', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MS' => 'Montserrat', 'MT' => 'Malta', 'MU' => 'Mauritius', 'MV' => 'Maldives', 'MW' => 'Malawi', 'MX' => 'Mexico', 'MY' => 'Malaysia', 'MZ' => 'Mozambique', 'NA' => 'Namibia', 'NC' => 'New Caledonia', 'NE' => 'Niger', 'NF' => 'Norfolk Island', 'NG' => 'Nigeria', 'NI' => 'Nicaragua', 'NL' => 'Netherlands', 'NO' => 'Norway', 'NP' => 'Nepal', 'NR' => 'Nauru', 'NU' => 'Niue', 'NZ' => 'New Zealand', 'OM' => 'Oman', 'PA' => 'Panama', 'PE' => 'Peru', 'PF' => 'French Polynesia', 'PG' => 'Papua New Guinea', 'PH' => 'Philippines', 'PK' => 'Pakistan', 'PL' => 'Poland', 'PM' => 'Saint Pierre And Miquelon', 'PN' => 'Pitcairn', 'PS' => 'Palestinian Territory, Occupied', 'PT' => 'Portugal', 'PY' => 'Paraguay', 'QA' => 'Qatar', 'RE' => 'Reunion', 'RO' => 'Romania', 'RS' => 'Serbia', 'RU' => 'Russia', 'RW' => 'Rwanda', 'SA' => 'Saudi Arabia', 'SB' => 'Solomon Islands', 'SC' => 'Seychelles', 'SD' => 'Sudan', 'SE' => 'Sweden', 'SG' => 'Singapore', 'SH' => 'Saint Helena', 'SI' => 'Slovenia', 'SJ' => 'Svalbard And Jan Mayen', 'SK' => 'Slovakia', 'SL' => 'Sierra Leone', 'SM' => 'San Marino', 'SN' => 'Senegal', 'SO' => 'Somalia', 'SR' => 'Suriname', 'ST' => 'Sao Tome And Principe', 'SV' => 'El Salvador', //        'SY' => 'Syria',
        'SZ' => 'Swaziland', 'TC' => 'Turks and Caicos Islands', 'TD' => 'Chad', 'TF' => 'French Southern Territories', 'TG' => 'Togo', 'TH' => 'Thailand', 'TJ' => 'Tajikistan', 'TK' => 'Tokelau', 'TL' => 'East Timor', 'TM' => 'Turkmenistan', 'TN' => 'Tunisia', 'TO' => 'Tonga', 'TR' => 'Turkey', 'TT' => 'Trinidad And Tobago', 'TV' => 'Tuvalu', 'TW' => 'Taiwan', 'TZ' => 'Tanzania', //        'UA' => 'Ukraine',
        'UG' => 'Uganda', 'UM' => 'United States Minor Outlying Islands', 'US' => 'United States', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VA' => 'Holy See (Vatican City State)', 'VC' => 'St. Vincent', 'VE' => 'Venezuela', 'VG' => 'Virgin Islands, British', 'VN' => 'Vietnam', 'VU' => 'Vanuatu', 'WF' => 'Wallis And Futuna', 'WS' => 'Samoa', 'YE' => 'Yemen', 'YT' => 'Mayotte', 'ZA' => 'South Africa', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe'];
    protected $_provinces = [//US,BR,CA,AU,MX,IT
        'US' => ['AL' => 'Alabama', 'AK' => 'Alaska', 'AS' => 'American Samoa', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'AE' => 'Armed Forces Middle East', 'AA' => 'Armed Forces Americas', 'AP' => 'Armed Forces Pacific', 'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'DC' => 'District of Columbia', 'FM' => 'Federated States Of Micronesia', 'FL' => 'Florida', 'GA' => 'Georgia', 'GU' => 'Guam', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MH' => 'Marshall Islands', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'MP' => 'Northern Mariana Islands', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PW' => 'Palau', 'PA' => 'Pennsylvania', 'PR' => 'Puerto Rico', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VI' => 'Virgin Islands', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming'], 'BR' => ['AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas', 'BA' => 'Bahia', 'CE' => 'Ceará', 'ES' => 'Espírito Santo', 'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul', 'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná', 'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte', 'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina', 'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins', 'DF' => 'Distrito Federal'], 'CA' => ['AB' => 'Alberta', 'BC' => 'British Columbia', 'MB' => 'Manitoba', 'NL' => 'Newfoundland and Labrador', 'NB' => 'New Brunswick', 'NS' => 'Nova Scotia', 'NT' => 'Northwest Territories', 'NU' => 'Nunavut', 'ON' => 'Ontario', 'PE' => 'Prince Edward Island', 'QC' => 'Quebec', 'SK' => 'Saskatchewan', 'YT' => 'Yukon Territory'], 'AU' => ['ACT' => 'Australian Capital Territory', 'NSW' => 'New South Wales', 'VIC' => 'Victoria', 'QLD' => 'Queensland', 'SA' => 'South Australia', 'TAS' => 'Tasmania', 'WA' => 'Western Australia', 'NT' => 'Northern Territory'], 'MX' => ['AGS' => 'Aguascalientes', 'BC' => 'Baja California', 'BCS' => 'Baja California Sur', 'CAMP' => 'Campeche', 'CHIS' => 'Chiapas', 'CHIH' => 'Chihuahua', 'DF' => 'Ciudad de México', 'COAH' => 'Coahuila', 'COL' => 'Colima', 'DGO' => 'Durango', 'GTO' => 'Guanajuato', 'GRO' => 'Guerrero', 'HGO' => 'Hidalgo', 'JAL' => 'Jalisco', 'MEX' => 'México', 'MICH' => 'Michoacán', 'MOR' => 'Morelos', 'NAY' => 'Nayarit', 'NL' => 'Nuevo León', 'OAX' => 'Oaxaca', 'PUE' => 'Puebla', 'QRO' => 'Querétaro', 'Q ROO' => 'Quintana Roo', 'SLP' => 'San Luis Potosí', 'SIN' => 'Sinaloa', 'SON' => 'Sonora', 'TAB' => 'Tabasco', 'TAMPS' => 'Tamaulipas', 'TLAX' => 'Tlaxcala', 'VER' => 'Veracruz', 'YUC' => 'Yucatán', 'ZAC' => 'Zacatecas'], 'IT' => ['AG' => 'Agrigento', 'AL' => 'Alessandria', 'AN' => 'Ancona', 'AO' => 'Aosta', 'AR' => 'Arezzo', 'AP' => 'Ascoli Piceno', 'AT' => 'Asti', 'AV' => 'Avellino', 'BA' => 'Bari', 'BT' => 'Barletta-Andria-Trani', 'BL' => 'Belluno', 'BN' => 'Benevento', 'BG' => 'Bergamo', 'BI' => 'Biella', 'BO' => 'Bologna', 'BZ' => 'Bolzano', 'BS' => 'Brescia', 'BR' => 'Brindisi', 'CA' => 'Cagliari', 'CL' => 'Caltanissetta', 'CB' => 'Campobasso', 'CI' => 'Carbonia-Iglesias', 'CE' => 'Caserta', 'CT' => 'Catania', 'CZ' => 'Catanzaro', 'CH' => 'Chieti', 'CO' => 'Como', 'CS' => 'Cosenza', 'CR' => 'Cremona', 'KR' => 'Crotone', 'CN' => 'Cuneo', 'EN' => 'Enna', 'FM' => 'Fermo', 'FE' => 'Ferrara', 'FI' => 'Firenze', 'FG' => 'Foggia', 'FC' => 'ForlÃ¬-Cesena', 'FR' => 'Frosinone', 'GE' => 'Genova', 'GO' => 'Gorizia', 'GR' => 'Grosseto', 'IM' => 'Imperia', 'IS' => 'Isernia', 'AQ' => 'L\'Aquila', 'SP' => 'La Spezia', 'LT' => 'Latina', 'LE' => 'Lecce', 'LC' => 'Lecco', 'LI' => 'Livorno', 'LO' => 'Lodi', 'LU' => 'Lucca', 'MC' => 'Macerata', 'MN' => 'Mantova', 'MS' => 'Massa-Carrara', 'MT' => 'Matera', 'VS' => 'Medio Campidano', 'ME' => 'Messina', 'MI' => 'Milano', 'MO' => 'Modena', 'MB' => 'Monza e Brianza', 'NA' => 'Napoli', 'NO' => 'Novara', 'NU' => 'Nuoro', 'OG' => 'Ogliastra', 'OT' => 'Olbia-Tempio', 'OR' => 'Oristano', 'PD' => 'Padova', 'PA' => 'Palermo', 'PR' => 'Parma', 'PV' => 'Pavia', 'PG' => 'Perugia', 'PU' => 'Pesaro e Urbino', 'PE' => 'Pescara', 'PC' => 'Piacenza', 'PI' => 'Pisa', 'PT' => 'Pistoia', 'PN' => 'Pordenone', 'PZ' => 'Potenza', 'PO' => 'Prato', 'RG' => 'Ragusa', 'RA' => 'Ravenna', 'RC' => 'Reggio Calabria', 'RE' => 'Reggio Emilia', 'RI' => 'Rieti', 'RN' => 'Rimini', 'RM' => 'Roma', 'RO' => 'Rovigo', 'SA' => 'Salerno', 'SS' => 'Sassari', 'SV' => 'Savona', 'SI' => 'Siena', 'SR' => 'Siracusa', 'SO' => 'Sondrio', 'TA' => 'Taranto', 'TE' => 'Teramo', 'TR' => 'Terni', 'TO' => 'Torino', 'TP' => 'Trapani', 'TN' => 'Trento', 'TV' => 'Treviso', 'TS' => 'Trieste', 'UD' => 'Udine', 'VA' => 'Varese', 'VE' => 'Venezia', 'VB' => 'Verbano-Cusio-Ossola', 'VC' => 'Vercelli', 'VR' => 'Verona', 'VV' => 'Vibo Valentia', 'VI' => 'Vicenza', 'VT' => 'Viterbo']
        /* 'DE' => [
          'NDS' => 'Niedersachsen',
          'BAW' => 'Baden-Württemberg',
          'BAY' => 'Bayern',
          'BER' => 'Berlin',
          'BRG' => 'Brandenburg',
          'BRE' => 'Bremen',
          'HAM' => 'Hamburg',
          'HES' => 'Hessen',
          'MEC' => 'Mecklenburg-Vorpommern',
          'NRW' => 'Nordrhein-Westfalen',
          'RHE' => 'Rheinland-Pfalz',
          'SAR' => 'Saarland',
          'SAS' => 'Sachsen',
          'SAC' => 'Sachsen-Anhalt',
          'SCN' => 'Schleswig-Holstein',
          'THE' => 'Thüringen'], */
        /* 'AT' => [
          'WI' => 'Wien',
          'NO' => 'Niederösterreich',
          'OO' => 'Oberösterreich',
          'SB' => 'Salzburg',
          'KN' => 'Kärnten',
          'ST' => 'Steiermark',
          'TI' => 'Tirol',
          'BL' => 'Burgenland',
          'VB' => 'Vorarlberg'], */
        /* 'CH' => [
          'AG' => 'Aargau',
          'AI' => 'Appenzell Innerrhoden',
          'AR' => 'Appenzell Ausserrhoden',
          'BE' => 'Bern',
          'BL' => 'Basel-Landschaft',
          'BS' => 'Basel-Stadt',
          'FR' => 'Freiburg',
          'GE' => 'Genf',
          'GL' => 'Glarus',
          'GR' => 'Graubünden',
          'JU' => 'Jura',
          'LU' => 'Luzern',
          'NE' => 'Neuenburg',
          'NW' => 'Nidwalden',
          'OW' => 'Obwalden',
          'SG' => 'St. Gallen',
          'SH' => 'Schaffhausen',
          'SO' => 'Solothurn',
          'SZ' => 'Schwyz',
          'TG' => 'Thurgau',
          'TI' => 'Tessin',
          'UR' => 'Uri',
          'VD' => 'Waadt',
          'VS' => 'Wallis',
          'ZG' => 'Zug',
          'ZH' => 'Zürich'], */
        /* 'ES' => [
          'A Coruсa' => 'A Coruña',
          'Alava' => 'Alava',
          'Albacete' => 'Albacete',
          'Alicante' => 'Alicante',
          'Almeria' => 'Almeria',
          'Asturias' => 'Asturias',
          'Avila' => 'Avila',
          'Badajoz' => 'Badajoz',
          'Baleares' => 'Baleares',
          'Barcelona' => 'Barcelona',
          'Burgos' => 'Burgos',
          'Caceres' => 'Caceres',
          'Cadiz' => 'Cadiz',
          'Cantabria' => 'Cantabria',
          'Castellon' => 'Castellon',
          'Ceuta' => 'Ceuta',
          'Ciudad Real' => 'Ciudad Real',
          'Cordoba' => 'Cordoba',
          'Cuenca' => 'Cuenca',
          'Girona' => 'Girona',
          'Granada' => 'Granada',
          'Guadalajara' => 'Guadalajara',
          'Guipuzcoa' => 'Guipuzcoa',
          'Huelva' => 'Huelva',
          'Huesca' => 'Huesca',
          'Jaen' => 'Jaen',
          'La Rioja' => 'La Rioja',
          'Las Palmas' => 'Las Palmas',
          'Leon' => 'Leon',
          'Lleida' => 'Lleida',
          'Lugo' => 'Lugo',
          'Madrid' => 'Madrid',
          'Malaga' => 'Malaga',
          'Melilla' => 'Melilla',
          'Murcia' => 'Murcia',
          'Navarra' => 'Navarra',
          'Ourense' => 'Ourense',
          'Palencia' => 'Palencia',
          'Pontevedra' => 'Pontevedra',
          'Salamanca' => 'Salamanca',
          'Santa Cruz de Tenerife' => 'Santa Cruz de Tenerife',
          'Segovia' => 'Segovia',
          'Sevilla' => 'Sevilla',
          'Soria' => 'Soria',
          'Tarragona' => 'Tarragona',
          'Teruel' => 'Teruel',
          'Toledo' => 'Toledo',
          'Valencia' => 'Valencia',
          'Valladolid' => 'Valladolid',
          'Vizcaya' => 'Vizcaya',
          'Zamora' => 'Zamora',
          'Zaragoza' => 'Zaragoza'], */
        /* 'FR' => [
          1 => 'Ain',
          2 => 'Aisne',
          3 => 'Allier',
          4 => 'Alpes-de-Haute-Provence',
          5 => 'Hautes-Alpes',
          6 => 'Alpes-Maritimes',
          7 => 'Ardèche',
          8 => 'Ardennes',
          9 => 'Ariège',
          10 => 'Aube',
          11 => 'Aude',
          12 => 'Aveyron',
          13 => 'Bouches-du-Rhône',
          14 => 'Calvados',
          15 => 'Cantal',
          16 => 'Charente',
          17 => 'Charente-Maritime',
          18 => 'Cher',
          19 => 'Corrèze',
          '2A' => 'Corse-du-Sud',
          '2B' => 'Haute-Corse',
          21 => 'Côte-d\'Or',
          22 => 'Côtes-d\'Armor',
          23 => 'Creuse',
          24 => 'Dordogne',
          25 => 'Doubs',
          26 => 'Drôme',
          27 => 'Eure',
          28 => 'Eure-et-Loir',
          29 => 'Finistère',
          30 => 'Gard',
          31 => 'Haute-Garonne',
          32 => 'Gers',
          33 => 'Gironde',
          34 => 'Hérault',
          35 => 'Ille-et-Vilaine',
          36 => 'Indre',
          37 => 'Indre-et-Loire',
          38 => 'Isère',
          39 => 'Jura',
          40 => 'Landes',
          41 => 'Loir-et-Cher',
          42 => 'Loire',
          43 => 'Haute-Loire',
          44 => 'Loire-Atlantique',
          45 => 'Loiret',
          46 => 'Lot',
          47 => 'Lot-et-Garonne',
          48 => 'Lozère',
          49 => 'Maine-et-Loire',
          50 => 'Manche',
          51 => 'Marne',
          52 => 'Haute-Marne',
          53 => 'Mayenne',
          54 => 'Meurthe-et-Moselle',
          55 => 'Meuse',
          56 => 'Morbihan',
          57 => 'Moselle',
          58 => 'Nièvre',
          59 => 'Nord',
          60 => 'Oise',
          61 => 'Orne',
          62 => 'Pas-de-Calais',
          63 => 'Puy-de-Dôme',
          64 => 'Pyrénées-Atlantiques',
          65 => 'Hautes-Pyrénées',
          66 => 'Pyrénées-Orientales',
          67 => 'Bas-Rhin',
          68 => 'Haut-Rhin',
          69 => 'Rhône',
          70 => 'Haute-Saône',
          71 => 'Saône-et-Loire',
          72 => 'Sarthe',
          73 => 'Savoie',
          74 => 'Haute-Savoie',
          75 => 'Paris',
          76 => 'Seine-Maritime',
          77 => 'Seine-et-Marne',
          78 => 'Yvelines',
          79 => 'Deux-Sèvres',
          80 => 'Somme',
          81 => 'Tarn',
          82 => 'Tarn-et-Garonne',
          83 => 'Var',
          84 => 'Vaucluse',
          85 => 'Vendée',
          86 => 'Vienne',
          87 => 'Haute-Vienne',
          88 => 'Vosges',
          89 => 'Yonne',
          90 => 'Territoire-de-Belfort',
          91 => 'Essonne',
          92 => 'Hauts-de-Seine',
          93 => 'Seine-Saint-Denis',
          94 => 'Val-de-Marne',
          95 => 'Val-d\'Oise'], */
        /*'RO' => [
            'AB' => 'Alba',
            'AR' => 'Arad',
            'AG' => 'Argeş',
            'BC' => 'Bacău',
            'BH' => 'Bihor',
            'BN' => 'Bistriţa-Năsăud',
            'BT' => 'Botoşani',
            'BV' => 'Braşov',
            'BR' => 'Brăila',
            'B' => 'Bucureşti',
            'BZ' => 'Buzău',
            'CS' => 'Caraş-Severin',
            'CL' => 'Călăraşi',
            'CJ' => 'Cluj',
            'CT' => 'Constanţa',
            'CV' => 'Covasna',
            'DB' => 'Dâmboviţa',
            'DJ' => 'Dolj',
            'GL' => 'Galaţi',
            'GR' => 'Giurgiu',
            'GJ' => 'Gorj',
            'HR' => 'Harghita',
            'HD' => 'Hunedoara',
            'IL' => 'Ialomiţa',
            'IS' => 'Iaşi',
            'IF' => 'Ilfov',
            'MM' => 'Maramureş',
            'MH' => 'Mehedinţi',
            'MS' => 'Mureş',
            'NT' => 'Neamţ',
            'OT' => 'Olt',
            'PH' => 'Prahova',
            'SM' => 'Satu-Mare',
            'SJ' => 'Sălaj',
            'SB' => 'Sibiu',
            'SV' => 'Suceava',
            'TR' => 'Teleorman',
            'TM' => 'Timiş',
            'TL' => 'Tulcea',
            'VS' => 'Vaslui',
            'VL' => 'Vâlcea',
            'VN' => 'Vrancea'],*/
        /*'FI' => [
            'Lappi' => 'Lappi',
            'Pohjois-Pohjanmaa' => 'Pohjois-Pohjanmaa',
            'Kainuu' => 'Kainuu',
            'Pohjois-Karjala' => 'Pohjois-Karjala',
            'Pohjois-Savo' => 'Pohjois-Savo',
            'Etelä-Savo' => 'Etelä-Savo',
            'Etelä-Pohjanmaa' => 'Etelä-Pohjanmaa',
            'Pohjanmaa' => 'Pohjanmaa',
            'Pirkanmaa' => 'Pirkanmaa',
            'Satakunta' => 'Satakunta',
            'Keski-Pohjanmaa' => 'Keski-Pohjanmaa',
            'Keski-Suomi' => 'Keski-Suomi',
            'Varsinais-Suomi' => 'Varsinais-Suomi',
            'Etelä-Karjala' => 'Etelä-Karjala',
            'Päijät-Häme' => 'Päijät-Häme',
            'Kanta-Häme' => 'Kanta-Häme',
            'Uusimaa' => 'Uusimaa',
            'Itä-Uusimaa' => 'Itä-Uusimaa',
            'Kymenlaakso' => 'Kymenlaakso',
            'Ahvenanmaa' => 'Ahvenanmaa'],*/
        /*'EE' => [
            'EE-37' => 'Harjumaa',
            'EE-39' => 'Hiiumaa',
            'EE-44' => 'Ida-Virumaa',
            'EE-49' => 'Jõgevamaa',
            'EE-51' => 'Järvamaa',
            'EE-57' => 'Läänemaa',
            'EE-59' => 'Lääne-Virumaa',
            'EE-65' => 'Põlvamaa',
            'EE-67' => 'Pärnumaa',
            'EE-70' => 'Raplamaa',
            'EE-74' => 'Saaremaa',
            'EE-78' => 'Tartumaa',
            'EE-82' => 'Valgamaa',
            'EE-84' => 'Viljandimaa',
            'EE-86' => 'Võrumaa'],*/
        /*'LV' => [
            'LV-DGV' => 'Daugavpils',
            'LV-JEL' => 'Jelgava',
            'Jēkabpils' => 'Jēkabpils',
            'LV-JUR' => 'Jūrmala',
            'LV-LPX' => 'Liepāja',
            'LV-LE' => 'Liepājas novads',
            'LV-REZ' => 'Rēzekne',
            'LV-RIX' => 'Rīga',
            'LV-RI' => 'Rīgas novads',
            'Valmiera' => 'Valmiera',
            'LV-VEN' => 'Ventspils',
            'Aglonas novads' => 'Aglonas novads',
            'LV-AI' => 'Aizkraukles novads',
            'Aizputes novads' => 'Aizputes novads',
            'Aknīstes novads' => 'Aknīstes novads',
            'Alojas novads' => 'Alojas novads',
            'Alsungas novads' => 'Alsungas novads',
            'LV-AL' => 'Alūksnes novads',
            'Amatas novads' => 'Amatas novads',
            'Apes novads' => 'Apes novads',
            'Auces novads' => 'Auces novads',
            'Babītes novads' => 'Babītes novads',
            'Baldones novads' => 'Baldones novads',
            'Baltinavas novads' => 'Baltinavas novads',
            'LV-BL' => 'Balvu novads',
            'LV-BU' => 'Bauskas novads',
            'Beverīnas novads' => 'Beverīnas novads',
            'Brocēnu novads' => 'Brocēnu novads',
            'Burtnieku novads' => 'Burtnieku novads',
            'Carnikavas novads' => 'Carnikavas novads',
            'Cesvaines novads' => 'Cesvaines novads',
            'Ciblas novads' => 'Ciblas novads',
            'LV-CE' => 'Cēsu novads',
            'Dagdas novads' => 'Dagdas novads',
            'LV-DA' => 'Daugavpils novads',
            'LV-DO' => 'Dobeles novads',
            'Dundagas novads' => 'Dundagas novads',
            'Durbes novads' => 'Durbes novads',
            'Engures novads' => 'Engures novads',
            'Garkalnes novads' => 'Garkalnes novads',
            'Grobiņas novads' => 'Grobiņas novads',
            'LV-GU' => 'Gulbenes novads',
            'Iecavas novads' => 'Iecavas novads',
            'Ikšķiles novads' => 'Ikšķiles novads',
            'Ilūkstes novads' => 'Ilūkstes novads',
            'Inčukalna novads' => 'Inčukalna novads',
            'Jaunjelgavas novads' => 'Jaunjelgavas novads',
            'Jaunpiebalgas novads' => 'Jaunpiebalgas novads',
            'Jaunpils novads' => 'Jaunpils novads',
            'LV-JL' => 'Jelgavas novads',
            'LV-JK' => 'Jēkabpils novads',
            'Kandavas novads' => 'Kandavas novads',
            'Kokneses novads' => 'Kokneses novads',
            'Krimuldas novads' => 'Krimuldas novads',
            'Krustpils novads' => 'Krustpils novads',
            'LV-KR' => 'Krāslavas novads',
            'LV-KU' => 'Kuldīgas novads',
            'Kārsavas novads' => 'Kārsavas novads',
            'Lielvārdes novads' => 'Lielvārdes novads',
            'LV-LM' => 'Limbažu novads',
            'Lubānas novads' => 'Lubānas novads',
            'LV-LU' => 'Ludzas novads',
            'Līgatnes novads' => 'Līgatnes novads',
            'Līvānu novads' => 'Līvānu novads',
            'LV-MA' => 'Madonas novads',
            'Mazsalacas novads' => 'Mazsalacas novads',
            'Mālpils novads' => 'Mālpils novads',
            'Mārupes novads' => 'Mārupes novads',
            'Naukšēnu novads' => 'Naukšēnu novads',
            'Neretas novads' => 'Neretas novads',
            'Nīcas novads' => 'Nīcas novads',
            'LV-OG' => 'Ogres novads',
            'Olaines novads' => 'Olaines novads',
            'Ozolnieku novads' => 'Ozolnieku novads',
            'LV-PR' => 'Preiļu novads',
            'Priekules novads' => 'Priekules novads',
            'Priekuļu novads' => 'Priekuļu novads',
            'Pārgaujas novads' => 'Pārgaujas novads',
            'Pāvilostas novads' => 'Pāvilostas novads',
            'Pļaviņu novads' => 'Pļaviņu novads',
            'Raunas novads' => 'Raunas novads',
            'Riebiņu novads' => 'Riebiņu novads',
            'Rojas novads' => 'Rojas novads',
            'Ropažu novads' => 'Ropažu novads',
            'Rucavas novads' => 'Rucavas novads',
            'Rugāju novads' => 'Rugāju novads',
            'Rundāles novads' => 'Rundāles novads',
            'LV-RE' => 'Rēzeknes novads',
            'Rūjienas novads' => 'Rūjienas novads',
            'Salacgrīvas novads' => 'Salacgrīvas novads',
            'Salas novads' => 'Salas novads',
            'Salaspils novads' => 'Salaspils novads',
            'LV-SA' => 'Saldus novads',
            'Saulkrastu novads' => 'Saulkrastu novads',
            'Siguldas novads' => 'Siguldas novads',
            'Skrundas novads' => 'Skrundas novads',
            'Skrīveru novads' => 'Skrīveru novads',
            'Smiltenes novads' => 'Smiltenes novads',
            'Stopiņu novads' => 'Stopiņu novads',
            'Strenču novads' => 'Strenču novads',
            'Sējas novads' => 'Sējas novads',
            'LV-TA' => 'Talsu novads',
            'LV-TU' => 'Tukuma novads',
            'Tērvetes novads' => 'Tērvetes novads',
            'Vaiņodes novads' => 'Vaiņodes novads',
            'LV-VK' => 'Valkas novads',
            'LV-VM' => 'Valmieras novads',
            'Varakļānu novads' => 'Varakļānu novads',
            'Vecpiebalgas novads' => 'Vecpiebalgas novads',
            'Vecumnieku novads' => 'Vecumnieku novads',
            'LV-VE' => 'Ventspils novads',
            'Viesītes novads' => 'Viesītes novads',
            'Viļakas novads' => 'Viļakas novads',
            'Viļānu novads' => 'Viļānu novads',
            'Vārkavas novads' => 'Vārkavas novads',
            'Zilupes novads' => 'Zilupes novads',
            'Ādažu novads' => 'Ādažu novads',
            'Ērgļu novads' => 'Ērgļu novads',
            'Ķeguma novads' => 'Ķeguma novads',
            'Ķekavas novads' => 'Ķekavas novads'],*/
        /*'LT' => [
            'LT-AL' => 'Alytaus Apskritis',
            'LT-KU' => 'Kauno Apskritis',
            'LT-KL' => 'Klaipėdos Apskritis',
            'LT-MR' => 'Marijampolės Apskritis',
            'LT-PN' => 'Panevėžio Apskritis',
            'LT-SA' => 'Šiaulių Apskritis',
            'LT-TA' => 'Tauragės Apskritis',
            'LT-TE' => 'Telšių Apskritis',
            'LT-UT' => 'Utenos Apskritis',
            'LT-VL' => 'Vilniaus Apskritis'],*/
        /*'HR' => [
            'HR-01' => 'Zagrebačka županija',
            'HR-02' => 'Krapinsko-zagorska županija',
            'HR-03' => 'Sisačko-moslavačka županija',
            'HR-04' => 'Karlovačka županija',
            'HR-05' => 'Varaždinska županija',
            'HR-06' => 'Koprivničko-križevačka županija',
            'HR-07' => 'Bjelovarsko-bilogorska županija',
            'HR-08' => 'Primorsko-goranska županija',
            'HR-09' => 'Ličko-senjska županija',
            'HR-10' => 'Virovitičko-podravska županija',
            'HR-11' => 'Požeško-slavonska županija',
            'HR-12' => 'Brodsko-posavska županija',
            'HR-13' => 'Zadarska županija',
            'HR-14' => 'Osječko-baranjska županija',
            'HR-15' => 'Šibensko-kninska županija',
            'HR-16' => 'Vukovarsko-srijemska županija',
            'HR-17' => 'Splitsko-dalmatinska županija',
            'HR-18' => 'Istarska županija',
            'HR-19' => 'Dubrovačko-neretvanska županija',
            'HR-20' => 'Međimurska županija',
            'HR-21' => 'Grad Zagreb'],*//*'IN' => [
            'AN' => 'Andaman and Nicobar Islands',
            'AP' => 'Andhra Pradesh',
            'AR' => 'Arunachal Pradesh',
            'AS' => 'Assam',
            'BR' => 'Bihar',
            'CH' => 'Chandigarh',
            'CT' => 'Chhattisgarh',
            'DN' => 'Dadra and Nagar Haveli',
            'DD' => 'Daman and Diu',
            'DL' => 'Delhi',
            'GA' => 'Goa',
            'GJ' => 'Gujarat',
            'HR' => 'Haryana',
            'HP' => 'Himachal Pradesh',
            'JK' => 'Jammu and Kashmir',
            'JH' => 'Jharkhand',
            'KA' => 'Karnataka',
            'KL' => 'Kerala',
            'LD' => 'Lakshadweep',
            'MP' => 'Madhya Pradesh',
            'MH' => 'Maharashtra',
            'MN' => 'Manipur',
            'ML' => 'Meghalaya',
            'MZ' => 'Mizoram',
            'NL' => 'Nagaland',
            'OR' => 'Odisha',
            'PY' => 'Puducherry',
            'PB' => 'Punjab',
            'RJ' => 'Rajasthan',
            'SK' => 'Sikkim',
            'TN' => 'Tamil Nadu',
            'TG' => 'Telangana',
            'TR' => 'Tripura',
            'UP' => 'Uttar Pradesh',
            'UT' => 'Uttarakhand',
            'WB' => 'West Bengal']*/];
    protected $_zip_formats = ['US' => ["#####", "#####-####"], 'IT' => ["#####"], 'GB' => ["@@## #@@", "@#@ #@@", "@@# #@@", "@@#@ #@@", "@## #@@", "@# #@@"], 'FR' => ["#####"], 'AF' => ["####"], 'AX' => ["#####", "AX-#####"], 'AL' => ["####"], 'DZ' => ["#####"], 'AD' => ["AD###", "#####"], 'AI' => ["AI-2640"], 'AR' => ["####", "@####@@@"], 'AM' => ["####"], 'AU' => ["####"], 'AT' => ["####"], 'AZ' => ["AZ ####"], 'BH' => ["###", "####"], 'BD' => ["####"], 'BB' => ["BB#####"], 'BY' => ["######"], 'BE' => ["####"], 'BM' => ["@@ ##", "@@ @@"], 'BT' => ["#####"], 'BA' => ["#####"], 'BR' => ["#####-###", "#####"], 'IO' => ["BBND 1ZZ"], 'VG' => ["VG####"], 'BN' => ["@@####"], 'BG' => ["####"], 'KH' => ["#####"], 'CA' => ["@#@ #@#"], 'CV' => ["####"], 'KY' => ["KY#-####"], 'CL' => ["#######", "###-####"], 'CN' => ["######"], 'CX' => ["####"], 'CC' => ["####"], 'CO' => ["######"], 'CR' => ["#####", "#####-####"], 'HR' => ["#####"], 'CU' => ["#####"], 'CY' => ["####"], 'CZ' => ["### ##"], 'DK' => ["####"], 'DO' => ["#####"], 'EC' => ["######"], 'EG' => ["#####"], 'SV' => ["####"], 'EE' => ["#####"], 'ET' => ["####"], 'FK' => ["FIQQ 1ZZ"], 'FO' => ["###"], 'FI' => ["#####"], 'GF' => ["973##"], 'PF' => ["987##"], 'GE' => ["####"], 'DE' => ["#####"], 'GI' => ["GX11 1AA"], 'GR' => ["### ##"], 'GL' => ["####"], 'GP' => ["971##"], 'GT' => ["#####"], 'GG' => ["GY# #@@", "GY## #@@"], 'GN' => ["###"], 'GW' => ["####"], 'HT' => ["####"], 'HN' => ["@@####", "#####"], 'HU' => ["####"], 'IS' => ["###"], 'IN' => ["######", "### ###"], 'ID' => ["#####"], 'IR' => ["##########", "#####-#####"], 'IQ' => ["#####"], 'IE' => ["@** ****", "@##"], 'IM' => ["IM# #@@", "IM## #@@"], 'IL' => ["#######"], 'JM' => ["##"], 'JP' => ["###-####", "###"], 'JE' => ["JE# #@@", "JE## #@@"], 'JO' => ["#####"], 'KZ' => ["######"], 'KE' => ["#####"], 'KW' => ["#####"], 'KG' => ["######"], 'LA' => ["#####"], 'LV' => ["LV-####"], 'LB' => ["#####", "#### ####"], 'LS' => ["###"], 'LR' => ["####"], 'LI' => ["####"], 'LT' => ["LT-#####"], 'LU' => ["####"], 'MK' => ["####"], 'MG' => ["###"], 'MY' => ["#####"], 'MV' => ["#####"], 'MT' => ["@@@ ####"], 'MQ' => ["972##"], 'MU' => ["#####"], 'YT' => ["976##"], 'MX' => ["#####"], 'MD' => ["MD####", "MD-####"], 'MC' => ["980##"], 'MN' => ["#####"], 'ME' => ["#####"], 'MA' => ["#####"], 'MZ' => ["####"], 'MM' => ["#####"], 'NP' => ["#####"], 'NL' => ["#### @@"], 'NC' => ["988##"], 'NZ' => ["####"], 'NI' => ["#####"], 'NE' => ["####"], 'NG' => ["######"], 'NF' => ["####"], 'NO' => ["####"], 'OM' => ["###"], 'PK' => ["#####"], 'PS' => ["###"], 'PA' => ["####"], 'PG' => ["###"], 'PY' => ["####"], 'PE' => ["#####", "PE #####"], 'PH' => ["####"], 'PN' => ["PCRN 1ZZ"], 'PL' => ["##-###"], 'PT' => ["####-###"], 'RE' => ["974##"], 'RO' => ["######"], 'RU' => ["######"], 'WS' => ["WS####"], 'SM' => ["4789#"], 'SA' => ["#####", "#####-####"], 'SN' => ["#####"], 'RS' => ["#####"], 'SG' => ["######"], 'SK' => ["### ##"], 'SI' => ["####", "SI-####"], 'SO' => ["@@ #####"], 'ZA' => ["####"], 'GS' => ["SIQQ 1ZZ"], 'KR' => ["###-###", "#####"], 'SS' => ["#####"], 'ES' => ["#####"], 'LK' => ["#####"], 'BL' => ["#####"], 'SH' => ["@@@@ 1ZZ"], 'LC' => ["LC## ###"], 'MF' => ["97150"], 'PM' => ["97500"], 'VC' => ["VC####"], 'SD' => ["#####"], 'SJ' => ["####"], 'SZ' => ["@###"], 'SE' => ["### ##"], 'CH' => ["####"], 'TW' => ["###", "###-##"], 'TJ' => ["######"], 'TZ' => ["#####"], 'TH' => ["#####"], 'TT' => ["######"], 'TN' => ["####"], 'TR' => ["#####"], 'TM' => ["######"], 'TC' => ["TKCA 1ZZ"], 'UA' => ["#####"], 'UY' => ["#####"], 'UZ' => ["######"], 'VA' => ["00120"], 'VE' => ["####", "####-@"], 'VN' => ["######"], 'WF' => ["986##"], 'ZM' => ["#####"]];
    protected $_phone_prefix = ['US' => ["1"], 'IT' => ["39"], 'GB' => ["44"], 'FR' => ["33"], 'AF' => ["93"], 'AL' => ["355"], 'DZ' => ["213"], 'AD' => ["376"], 'AO' => ["244"], 'AI' => ["1-264"], 'AG' => ["1-268"], 'AR' => ["54"], 'AM' => ["374"], 'AW' => ["297"], 'AU' => ["61"], 'AT' => ["43"], 'AZ' => ["994"], 'BS' => ["1-242"], 'BH' => ["973"], 'BD' => ["880"], 'BB' => ["1-246"], 'BY' => ["375"], 'BE' => ["32"], 'BZ' => ["501"], 'BJ' => ["229"], 'BM' => ["1-441"], 'BT' => ["975"], 'BO' => ["591"], 'BA' => ["387"], 'BW' => ["267"], 'BR' => ["55"], 'IO' => ["246"], 'VG' => ["1-284"], 'BN' => ["673"], 'BG' => ["359"], 'BF' => ["226"], 'BI' => ["257"], 'KH' => ["855"], 'CM' => ["237"], 'CA' => ["1"], 'CV' => ["238"], 'KY' => ["1-345"], 'CF' => ["236"], 'TD' => ["235"], 'CL' => ["56"], 'CN' => ["86"], 'CX' => ["61"], 'CC' => ["61"], 'CO' => ["57"], 'KM' => ["269"], 'CG' => ["242"], 'CD' => ["243"], 'CK' => ["682"], 'CR' => ["506"], 'HR' => ["385"], 'CU' => ["53"], 'CW' => ["599"], 'CY' => ["357"], 'CZ' => ["420"], 'CI' => ["225"], 'DK' => ["45"], 'DJ' => ["253"], 'DM' => ["1-767"], 'DO' => ["1-809", "1-829", "1-849"], 'EC' => ["593"], 'EG' => ["20"], 'SV' => ["503"], 'GQ' => ["240"], 'ER' => ["291"], 'EE' => ["372"], 'ET' => ["251"], 'FK' => ["500"], 'FO' => ["298"], 'FJ' => ["679"], 'FI' => ["358"], 'PF' => ["689"], 'GA' => ["241"], 'GM' => ["220"], 'GE' => ["995"], 'DE' => ["49"], 'GH' => ["233"], 'GI' => ["350"], 'GR' => ["30"], 'GL' => ["299"], 'GD' => ["1-473"], 'GT' => ["502"], 'GG' => ["44-1481"], 'GN' => ["224"], 'GW' => ["245"], 'GY' => ["592"], 'HT' => ["509"], 'HN' => ["504"], 'HK' => ["852"], 'HU' => ["36"], 'IS' => ["354"], 'IN' => ["91"], 'ID' => ["62"], 'IR' => ["98"], 'IQ' => ["964"], 'IE' => ["353"], 'IM' => ["44-1624"], 'IL' => ["972"], 'JM' => ["1-876"], 'JP' => ["81"], 'JE' => ["44-1534"], 'JO' => ["962"], 'KZ' => ["7"], 'KE' => ["254"], 'KI' => ["686"], 'XK' => ["383"], 'KW' => ["965"], 'KG' => ["996"], 'LA' => ["856"], 'LV' => ["371"], 'LB' => ["961"], 'LS' => ["266"], 'LR' => ["231"], 'LY' => ["218"], 'LI' => ["423"], 'LT' => ["370"], 'LU' => ["352"], 'MO' => ["853"], 'MK' => ["389"], 'MG' => ["261"], 'MW' => ["265"], 'MY' => ["60"], 'MV' => ["960"], 'ML' => ["223"], 'MT' => ["356"], 'MR' => ["222"], 'MU' => ["230"], 'YT' => ["262"], 'MX' => ["52"], 'MD' => ["373"], 'MC' => ["377"], 'MN' => ["976"], 'ME' => ["382"], 'MS' => ["1-664"], 'MA' => ["212"], 'MZ' => ["258"], 'MM' => ["95"], 'NA' => ["264"], 'NR' => ["674"], 'NP' => ["977"], 'NL' => ["31"], 'AN' => ["599"], 'NC' => ["687"], 'NZ' => ["64"], 'NI' => ["505"], 'NE' => ["227"], 'NG' => ["234"], 'NU' => ["683"], 'KP' => ["850"], 'NO' => ["47"], 'OM' => ["968"], 'PK' => ["92"], 'PS' => ["970"], 'PA' => ["507"], 'PG' => ["675"], 'PY' => ["595"], 'PE' => ["51"], 'PH' => ["63"], 'PN' => ["64"], 'PL' => ["48"], 'PT' => ["351"], 'QA' => ["974"], 'RE' => ["262"], 'RO' => ["40"], 'RU' => ["7"], 'RW' => ["250"], 'WS' => ["685"], 'SM' => ["378"], 'ST' => ["239"], 'SA' => ["966"], 'SN' => ["221"], 'RS' => ["381"], 'SC' => ["248"], 'SL' => ["232"], 'SG' => ["65"], 'SX' => ["1-721"], 'SK' => ["421"], 'SI' => ["386"], 'SB' => ["677"], 'SO' => ["252"], 'ZA' => ["27"], 'KR' => ["82"], 'SS' => ["211"], 'ES' => ["34"], 'LK' => ["94"], 'BL' => ["590"], 'SH' => ["290"], 'KN' => ["1-869"], 'LC' => ["1-758"], 'MF' => ["590"], 'PM' => ["508"], 'VC' => ["1-784"], 'SD' => ["249"], 'SR' => ["597"], 'SJ' => ["47"], 'SZ' => ["268"], 'SE' => ["46"], 'CH' => ["41"], 'SY' => ["963"], 'TW' => ["886"], 'TJ' => ["992"], 'TZ' => ["255"], 'TH' => ["66"], 'TL' => ["670"], 'TG' => ["228"], 'TK' => ["690"], 'TO' => ["676"], 'TT' => ["1-868"], 'TN' => ["216"], 'TR' => ["90"], 'TM' => ["993"], 'TC' => ["1-649"], 'TV' => ["688"], 'UG' => ["256"], 'UA' => ["380"], 'AE' => ["971"], 'UY' => ["598"], 'UZ' => ["998"], 'VU' => ["678"], 'VA' => ["379"], 'VE' => ["58"], 'VN' => ["84"], 'WF' => ["681"], 'EH' => ["212"], 'YE' => ["967"], 'ZM' => ["260"], 'ZW' => ["263"]];

    const ADDRESS_FIELDS = ['phone' => 'phone number', 'full_name' => 'full name', 'first_name' => 'first name', 'last_name' => 'last name', 'company' => 'company', 'address1' => 'address', 'address2' => 'address optional', 'city' => 'city', 'province' => 'state/province', 'province_code' => 'province code', 'country' => 'country', 'country_code' => 'country code', 'zip' => 'ZIP/Postal code'];
    const COUNTRY_ZIP_CODE_SPECIAL = ['US', 'JP', 'PL', 'PT', 'LT'];
    const ADDRESS_CONTACT_FIELDS = ['phone', 'full_name', 'company'];
    const ADDRESS_REQUIRE_FIELDS = ['full_name', 'phone', 'address1', 'city', 'province', 'zip', 'country'];

    public function __construct() {
        parent::__construct();
    }

    public function verifyAddress($address, $country_code = '') {
        foreach ($address as $field_name => $field_value) {
            $field_value = trim($field_value);

            switch ($field_name) {
                case 'full_name':
                case 'address1':
                case 'city':
                case 'country':
                case 'phone':
                    if ($field_value === '') {
                        throw new Exception(ucfirst(static::ADDRESS_FIELDS[$field_name]) . ' is empty');
                    }
                    break;
            }

            if ($field_name == 'full_name') {
                $field_value = preg_replace('/\s{2,}/', ' ', $field_value);
                $field_value = explode(' ', $field_value);

                if (count($field_value) < 2) {
                    throw new Exception('Your Name must include a First and Last Name');
                }

                $field_value = implode(' ', $field_value);
            }

            $address[$field_name] = $field_value;
        }

        if (isset($address['phone'])) {
            $address['phone'] = preg_replace('/[^\d]/', '', $address['phone']);

            if (!$address['phone']) {
                throw new Exception("Phone number is incorrect");
            }
        }

        if (isset($address['country'])) {
            $country_code = $this->getCountryCode($address['country']);

            if (!$country_code) {
                throw new Exception("Country [{$address['country']}] is not exists");
            }

            $address['country_code'] = $country_code;
        }

        if (isset($address['province'])) {
            if (!$country_code) {
                throw new Exception('You need provide country code to verify province');
            }

            if (!$this->getCountryTitle($country_code)) {
                throw new Exception("Country code [{$country_code}] is not exists");
            }

            if ($address['province'] !== null && $address['province'] !== '') {
                if (!$this->verifyProvince($country_code, $address['province'])) {
                    throw new Exception("Province is incorrect");
                }

                $address['province_code'] = $this->getProvinceCode($country_code, $address['province']);
            } else {
                $address['province'] = null;
                $address['province_code'] = null;
            }
        }

        if (isset($address['zip'])) {
            if (!$country_code) {
                throw new Exception('You need provide country code to verify ZIP/Postal code');
            }

            if (!$this->getCountryTitle($country_code)) {
                throw new Exception("Country code [{$country_code}] is not exists");
            }

            //            if (!$this->verifyZipCode($country_code, $address['zip'], true, true)) {
            if ($address['zip'] == '') {
                throw new Exception("ZIP/Postal code is incorrect");
            }
        }

        return $address;
    }

    public function getCountryCollection() {
        static $collection = null;

        if ($collection === null) {
            $collection = OSC::model('core/country_country')->getCollection()->load();
        }

        return $collection;
    }

    public function getProvinceCollection() {
        static $collection = null;

        if ($collection === null) {
            $collection = OSC::model('core/country_province')->getCollection()->load();
        }

        return $collection;
    }

    public function getCountries() {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        $cached = [];

        foreach ($this->getCountryCollection() as $country) {
            $cached[$country->data['country_code']] = $country->data['country_name'];
        }

        asort($cached);

        return $cached;
    }

    public function getPhonePrefixCountries() {
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        $cached = [];

        foreach ($this->getCountryCollection() as $country) {
            $cached[$country->data['country_code']] = array_shift($country->data['phone_prefix']);
        }

        return $cached;
    }

    public function getCountriesActive(){
        static $cached = null;

        if ($cached !== null) {
            return $cached;
        }

        $cached = [];

        $collection = OSC::model('core/country_country')->getCollection()->addCondition('status', self::STATUS_ACTIVE)->load();

        foreach ($collection as $country) {
            $cached[$country->data['country_code']] = $country->data['country_name'];
        }

        asort($cached);

        return $cached;
    }

    public function checkCountryDeactive($country){
        $collection = OSC::model('core/country_country')
            ->getCollection()
            ->addCondition('country_name', $country, OSC_Database::OPERATOR_EQUAL)
            ->addCondition('status', self::STATUS_DEACTIVE, OSC_Database::OPERATOR_EQUAL)
            ->load();

        return count($collection->toArray());
    }

    public function getCountryCode($country_title) {
        return array_search($country_title, $this->getCountries());
    }

    public function getCountryTitle($country_code) {
        return isset($this->getCountries()[$country_code]) ? $this->getCountries()[$country_code] : null;
    }

    public function getProvinces($country_code = null) {
        static $cached = null;

        if ($cached !== null) {
            return $country_code ? (isset($cached[$country_code]) ? $cached[$country_code] : []) : $cached;
        }

        $cached = [];

        foreach ($this->getProvinceCollection() as $province) {
            $cached[$province->data['country_code']][$province->data['province_code']] = $province->data['province_name'];
        }

        return $country_code ? (isset($cached[$country_code]) ? $cached[$country_code] : []) : $cached;
    }

    public function getProvinceCode($country_code, $province) {
        $provinces = $this->getProvinces($country_code);

        foreach ($provinces as $province_code => $province_title) {
            if ($province_title == $province) {
                return $province_code;
            }
        }

        return '';
    }

    public function getProvinceTitle($country_code, $province_code) {
        $provinces = $this->getProvinces($country_code);

        return isset($provinces[$province_code]) ? $provinces[$province_code] : '';
    }

    public function verifyProvince($country_code, $province_title) {
        $provinces = $this->getProvinces($country_code);

        return count($provinces) > 0 ? (array_search($province_title, $provinces) !== false) : true;
    }

    public function verifyProvinceCode($country_code, $province_code) {
        return $this->getProvinceTitle($country_code, $province_code) == '';
    }

    public function getZipFormat($country_code) {
        static $cached = null;

        if ($cached !== null) {
            return isset($cached[$country_code]) ? $cached[$country_code] : '';
        }

        $cached = [];

        foreach ($this->getCountryCollection() as $country) {
            $cached[$country->data['country_code']] = $country->data['zip_formats'];
        }

        return isset($cached[$country_code]) ? $cached[$country_code] : '';
    }

    public function verifyZipCode($country_code, $zip_code, $ignore_spaces = false, $ignore_special_chars = false) {
        if (!isset($this->getZipFormat()[$country_code])) {
            return true;
        }

        if ($ignore_special_chars) {
            $zip_code = str_replace(['-', '_', ' '], '', $zip_code);
        }

        foreach ($this->getZipFormat[$country_code] as $format) {
            if (preg_match($this->_parseZipCodeRegexPattern($format, $ignore_spaces, $ignore_special_chars), $zip_code)) {
                return true;
            }
        }

        return false;
    }

    public function getBlockCountries() {
        $block_countries = OSC::helper('core/setting')->get('shipping/block_countries');
        return $block_countries ? $block_countries : [];
    }

    public function getCountryCodePlaceOfManufacture() {
        $place_of_manufacture_enable = OSC::helper('core/setting')->get('place_of_manufacture/enable', 0);
        if ($place_of_manufacture_enable) {
            $location_country_code = OSC::helper('core/common')->getCustomerCountryCodeCookie();

            if (!$location_country_code) {
                $location = OSC::helper('catalog/common')->getCustomerIPLocation();
                $location_country_code = $location['country_code'] ?? '';
            }

            $place_of_manufacture_default = OSC::helper('core/setting')->get('place_of_manufacture/default', '');
            $place_of_manufacture = $this->__parseSettingPlaceOfManufacture();

            if ($location_country_code && isset($place_of_manufacture[$location_country_code])) {
                return $place_of_manufacture[$location_country_code];
            }

            if ($place_of_manufacture_default) {
                return $place_of_manufacture_default;
            }
        }
        return '';
    }

    protected function __parseSettingPlaceOfManufacture() {
        $place_of_manufacture_raw = OSC::helper('core/setting')->get('place_of_manufacture');
        $place_of_manufacture_raw = OSC::decode($place_of_manufacture_raw);
        $place_of_manufacture = [];
        if (is_array($place_of_manufacture_raw) && count($place_of_manufacture_raw) > 0) {
            foreach ($place_of_manufacture_raw as $manufactures) {
                if (isset($manufactures['country_place_of_manufacture'][0])) {
                    foreach ($manufactures['country_customer'] as $country_customer) {
                        $place_of_manufacture[$country_customer] = $manufactures['country_place_of_manufacture'][0];
                    }
                }
            }
        }
        return $place_of_manufacture;
    }

    protected function _parseZipCodeRegexPattern($format, $ignore_spaces = false, $ignore_special_chars = false) {
        if ($ignore_special_chars) {
            $format = str_replace(['-', '_'], '', $format);
        }

        $pattern = str_replace('#', '\d', $format);
        $pattern = str_replace('@', '[a-zA-Z]', $pattern);
        $pattern = str_replace('*', '[a-zA-Z0-9]', $pattern);

        if ($ignore_spaces) {
            $pattern = str_replace(' ', ' ?', $pattern);
        }

        return '/^' . $pattern . '$/';
    }

    public function getPhonePrefixFromCountryCode($country_code) {
        static $cached = null;

        if ($cached !== null) {
            return isset($cached[$country_code]) ? $cached[$country_code] : '';
        }

        $cached = [];

        foreach ($this->getCountryCollection() as $country) {
            $cached[$country->data['country_code']] = $country->data['zip_formats'];
        }

        return isset($cached[$country_code]) ? $cached[$country_code] : '';
    }

    public function getLocationGroup($country_code, $province_code, $flag_feed = false) {
        static $cached = [];

        $cached_key = $country_code . ':' . $province_code . ':' . ($flag_feed ? 1 : 0);
        if (isset($cached[$cached_key])) {
            return $cached[$cached_key];
        }

        $country = $this->getCountryCollection()->getItemByUkey(strtoupper($country_code));

        if(! $country) {
            throw new Exception('Unable to find country ' . $country_code);
        }

        $province = null;

        if ($province_code) {
            try {
                $province = $this->getCountryProvince($country_code, $province_code);
            } catch (Exception $ex) {

            }
        }

        $result_group = [];

        $flag_get_all_province = $flag_feed || is_null($province);

        try {
            $countries_has_province = $this->getCountryHasProvince();
            if ($flag_get_all_province && in_array($country_code, $countries_has_province)) {
                $province_ids = OSC::model('core/country_province')
                    ->getCollection()
                    ->addCondition('country_code', $country_code, OSC_Database::OPERATOR_EQUAL)
                    ->addField('id')
                    ->load()
                    ->toArray();
                $province_ids = array_column($province_ids, 'id');
                $province_ids_str = implode('|', $province_ids);

                $DB = OSC::core('database');
                $query_country = "'%\"c" . $country->getId() . "\"%'";
                $query_location_group = <<<EOF
SELECT id 
FROM osc_location_group 
WHERE parsed_data RLIKE '"p({$province_ids_str})"' OR parsed_data LIKE {$query_country};
EOF;
                $DB->query($query_location_group, null, 'fetch_location_group');
                $collection_location_ids = $DB->fetchArrayAll('fetch_location_group');
                $DB->free('fetch_location_group');
                $location_ids = array_column($collection_location_ids, 'id');
            } else {
                $collection_location_ids = OSC::model('core/country_group')
                    ->getCollection()
                    ->addCondition('parsed_data', '%"' . ($province ? ('p' . $province->getId()) : ('c' . $country->getId())) . '"%', OSC_Database::OPERATOR_LIKE)
                    ->addField('id')
                    ->load()
                    ->toArray();
                $location_ids = array_column($collection_location_ids, 'id');
            }

            foreach ($location_ids as $location_id) {
                $result_group[] = 'g' . $location_id;
            }

        } catch (Exception $ex) {

        }

        $cached[$cached_key] = ['country' => 'c' . $country->getId(), 'province' => $province ? ('p' . $province->getId()) : null, 'group' => $result_group];

        return $cached[$cached_key];
    }

    public function getCountryHasProvince()
    {
        static $cached = [];
        if (is_array($cached) && count($cached) > 0) {
            return $cached;
        }

        try {
            $DB = OSC::core('database');
            $DB->query("SELECT DISTINCT country_code FROM osc_location_province", null, 'fetch_location_province');
            $countries_has_province = $DB->fetchArrayAll('fetch_location_province');
            $DB->free('fetch_location_province');
            $cached = array_column($countries_has_province, 'country_code');
        } catch (Exception $ex) {

        }

        return $cached;
    }

    public function getCountry($country_code) {
        $country = $this->getCountryCollection()->getItemByUkey(strtoupper($country_code));

        if (!$country) {
            throw new Exception('Country ' . $country_code . ' is not exists');
        }

        return $country;
    }

    public function getCountryProvince($country_code, $province_code) {
        $country_code = strtoupper($country_code);
        $province_code = strtoupper($province_code);

        foreach ($this->getProvinceCollection() as $province) {
            if ($province->data['country_code'] == $country_code && $province->data['province_code'] == $province_code) {
                return $province;
            }
        }

        throw new Exception('Province is not exists');
    }

    public function getCountryGroup($group_id, $useCache = false) {
        static $cached = [];

        $group_id = intval($group_id);

        if (isset($cached[$group_id])) {
            return $cached[$group_id];
        }

        $cache_key = __FUNCTION__ . "|group_id:,{$group_id},|";
        if ($useCache && ($cache = OSC::core('cache')->get($cache_key)) !== false) {
            $cached[$group_id] = OSC::model('core/country_group')->bind($cache);
            return $cached[$group_id];
        }

        $cached[$group_id] = OSC::model('core/country_group')->load($group_id);
        OSC::core('cache')->set($cache_key, $cached[$group_id]->data, OSC_CACHE_TIME);

        return $cached[$group_id];
    }

    public function checkCountryProvinceInLocation($country_code, $province_code, $location) {
        static $cached = [];

        $cache_key = $country_code . '-' . $province_code . ':' . $location;

        if (isset($cached[$cache_key])) {
            return $cached[$cache_key];
        }

        $flag = false;
        $country_code = strtoupper($country_code);
        $province_code = strtoupper($province_code);

        try {
            $country = $this->getCountry($country_code);

            try {
                $province = $this->getCountryProvince($country_code, $province_code);
            } catch (Exception $ex) {
                $province = null;
            }

            switch (substr($location, 0, 1)) {
                case 'g':
                    $group = $this->getCountryGroup(substr($location, 1), true);
                    $flag = in_array($province ? ('p' . $province->getId()) : ('c' . $country->getId()), $group->data['parsed_data']);
                    break;
                case 'c':
                    $country_id = intval(substr($location, 1));
                    $flag = $country->getId() > 0 && $country->getId() == $country_id;
                    break;
                case 'p':
                    $province_id = intval(substr($location, 1));
                    $flag = $province != '' && $province->getId() == $province_id;
                    break;
                default:
                    throw new Exception('Format location is incorrect');
            }

        } catch (Exception $ex) {

        }

        $cached[$cache_key] = $flag;

        return $cached[$cache_key];
    }

    public function compareLocation($location, $location_compare) {
        if ($location_compare === '*') {
            return true;
        }
        $data_parsed_location = [];
        if (substr($location, 0, 1) == 'g') {
            $group_id = intval(substr($location, 1));
            $group = OSC::model('core/country_group');
            try {
                $group->load($group_id);
            } catch (Exception $ex) {
                return false;
            }
            $data_parsed_location = $group->data['parsed_data'];
        } else {
            $data_parsed_location = [$location];
        }
        $data_parsed_location_compare = [];
        if (substr($location_compare, 0, 1) == 'g') {
            $group_id = intval(substr($location_compare, 1));
            $group = OSC::model('core/country_group');
            try {
                $group->load($group_id);
            } catch (Exception $ex) {
                return false;
            }
            $data_parsed_location_compare = $group->data['parsed_data'];
        } else {
            $data_parsed_location_compare = [$location_compare];
        }
        return !empty(array_intersect($data_parsed_location, $data_parsed_location_compare));
    }

    public function getCountryCodeByLocation($locations = []) {
        if (count($locations) < 1) {
            return [];
        }

        $country_province_ids = [];

        foreach ($locations as $location) {
            if (substr($location, 0, 1) == 'g') {
                try {
                    $group = $this->getCountryGroup(intval(substr($location, 1)), true);
                } catch (Exception $ex) {
                    continue;
                }

                $country_province_ids = array_merge($country_province_ids, $group->data['parsed_data']);
            } else {
                $country_province_ids[] = $location;
            }
        }

        $country_codes = [];

        foreach ($country_province_ids as $country_province_id) {
            if (substr($country_province_id, 0, 1) == 'c') {
                $country = $this->getCountryCollection()->getItemByPK(substr($country_province_id, 1));

                if ($country) {
                    $country_codes[] = $country->data['country_code'];
                }
            } else {
                $province = $this->getProvinceCollection()->getItemByPK(substr($country_province_id, 1));

                if ($province) {
                    $country_codes[] = $province->data['country_code'];
                }
            }
        }

        return array_unique($country_codes);
    }

    public function getNameByLocation($location = null) {
        $result = '';

        try {
            if ($location === null) {
                throw new Exception('Location is not empty');
            }

            $location_id = intval(substr($location, 1));

            if ($location_id < 1) {
                throw new Exception('Location is incorrect format');
            }

            switch (substr($location, 0, 1)) {
                case "g":
                    $group = $this->getCountryGroup($location_id, true);
                    $result = $group->data['group_name'];
                    break;
                case "c":
                    $country = $this->getCountryCollection()->getItemByPK($location_id);

                    if (!$country) {
                        throw new Exception('Not have group');
                    }

                    $result = $country->data['country_name'];
                    break;
                case "p":
                    $province = $this->getProvinceCollection()->getItemByPK($location_id);

                    if (!$province) {
                        throw new Exception('Not have group');
                    }

                    $result = $province->data['province_name'];
                    break;
                default:
                    throw new Exception('Location is incorrect format');
                    break;
            }
        } catch (Exception $ex) {

        }

        return $result;
    }

    /**
     * Get parse location data level country, province (Ex: c1, c2, p1, p2)
     * @param $location_data
     * @return void|null
     * @throws Exception
     */
//    public function getParsedDataByLocation($location_data) {
//        $location_id = intval(substr($location_data, 1));
//
//        if (!isset($location_data) || is_null($location_data) || $location_data === '' || $location_id < 1) {
//            throw new Exception('Location data is not exists!');
//        }
//
//        $result = null;
//        switch (substr($location_data, 0, 1)) {
//            case 'g':
//                try {
//                    $group = OSC::model('core/country_group')->load($location_id);
//
//                    $result = $group->data['parsed_data'];
//                } catch (Exception $ex) {
//                    throw new Exception('Location group is not exists!');
//                }
//                break;
//            case 'c':
//                try {
//                    $country = OSC::model('core/country_country')->load($location_id);
//
//                    $result =  ['c'. $country->getId()];
//                } catch (Exception $ex) {
//                    throw new Exception('Location country is not exists!');
//                }
//                break;
//            case 'p':
//                try {
//                    $province = OSC::model('core/country_province')->load($location_id);
//
//                    $result =  ['p'. $province->getId()];
//                } catch (Exception $ex) {
//                    throw new Exception('Location province is not exists!');
//                }
//                break;
//            default:
//                break;
//        }
//
//        return $result;
//    }

    /**
     * @param $datas
     * @param $provinces
     * @return array
     * @throws OSC_Database_Model_Exception
     */
    public function dataPreview($datas, $provinces) {
        $result = [];

        foreach ($datas as $data) {
            if (substr($data, 0, 1) == 'c') {
                $country_id = intval(substr($data, 1));
                $country = OSC::model('core/country_country')->load($country_id);

                $result[$country->data['country_code']] = isset($provinces[$country->data['country_code']]) ?
                    $provinces[$country->data['country_code']] :
                    [];
            } else if (substr($data, 0, 1) == 'p') {
                $province_id = intval(substr($data, 1));

                $province = OSC::model('core/country_province')->load($province_id);

                if (!in_array('c' . $province->data['country_id'], $datas)) {
                    $result[$province->data['country_code']][] = $province->data['province_code'];
                }

            }
        }

        return $result;
    }

    public function getParsedDataByLocation($location_data){
        if (!is_array($location_data)) {
            return [];
        }
        $includes = isset($location_data['includes']) ? $location_data['includes'] : [] ;
        $excludes = isset($location_data['excludes']) ? $location_data['excludes'] : [];
        $data_excludes = [];
        if (in_array('*', $excludes)) {
            $countries = OSC::model('core/country_country')->getCollection()->addCondition('status', Helper_Core_Country::STATUS_ACTIVE)->addField('id')->load()->toArray();

            $country_ids = array_column($countries, 'id');

            foreach ($country_ids as $country_id) {
                $data_excludes[] = 'c' . $country_id;
            }
            $provinces = OSC::model('core/country_province')->getCollection()->addField('id')->load()->toArray();

            $province_ids = array_column($provinces, 'id');

            foreach ($province_ids as $province_id) {
                $data_excludes[] = 'p' . $province_id;
            }
        } else {
            foreach ($excludes as $exclude) {
                if (substr($exclude, 0, 1) == 'g') {
                    $group_id = intval(substr($exclude, 1));
                    $group = OSC::model('core/country_group')->load($group_id);
                    $data_excludes = array_merge($data_excludes, $group->data['parsed_data']);
                } else {
                    $data_excludes[] = $exclude;
                }
            }
        }

        $data_excludes = array_unique($data_excludes);
        $result_excludes = [];

        foreach ($data_excludes as $exclude) {
            if (substr($exclude, 0, 1) == 'c') {
                $country_id = intval(substr($exclude, 1));

                $result_excludes[] = $exclude;

                $provinces = OSC::model('core/country_province')->getCollection()->addCondition('country_id', $country_id, OSC_Database::OPERATOR_EQUAL)->addField('id')->load()->toArray();

                $province_ids = array_column($provinces, 'id');

                foreach ($province_ids as $province_id) {
                    $result_excludes[] = 'p' . $province_id;
                }
            } else {
                $province_id = intval(substr($exclude, 1));
                try {
                    $province = OSC::model('core/country_province')->load($province_id);
                } catch (Exception $ex) {
                    continue;
                }
                $result_excludes[] = 'c' . $province->data['country_id'];

                $result_excludes[] = $exclude;
            }
        }

        $result_excludes = array_unique($result_excludes);

        $data_includes = [];
        $all_provinces_country = [];

        if (count($includes) < 1 || in_array('*', $includes)) {
            $countries = OSC::model('core/country_country')->getCollection()->addField('id')->load()->toArray();

            $country_ids = array_column($countries, 'id');

            foreach ($country_ids as $country_id) {
                $data_includes[] = 'c' . $country_id;
            }
            $provinces = OSC::model('core/country_province')->getCollection()->addField('id')->load()->toArray();

            $province_ids = array_column($provinces, 'id');

            foreach ($province_ids as $province_id) {
                $data_includes[] = 'p' . $province_id;
            }
        } else {
            foreach ($includes as $include) {
                if (substr($include, 0, 1) == 'g') {
                    $group_id = substr($include, 1);
                    $group = OSC::model('core/country_group')->load($group_id);
                    $data_includes = array_merge($data_includes, $group->data['parsed_data']);
                } elseif (substr($include, 0, 1) == 'c') {
                    $data_includes[] = $include;

                    $country_id = substr($include, 1);

                    $provinces = OSC::model('core/country_province')->getCollection()->addCondition('country_id',$country_id, OSC_Database::OPERATOR_EQUAL)->addField('id')->load()->toArray();

                    $province_ids = array_column($provinces, 'id');

                    foreach ($province_ids as $province_id) {
                        $all_provinces_country[] = $province_id;
                        $data_includes[] = 'p'.$province_id;
                    }
                } else {
                    $data_includes[] = $include;
                }
            }
        }

        $data_includes = array_unique($data_includes);

        return array_values(array_diff($data_includes, $result_excludes));
    }

    /**
     * Check location data is exists
     * @param $location_data
     * @return boolean
     */
    public function isLocationExists($location_data) {
        $location_id = intval(substr($location_data, 1));

        if (!isset($location_data) || is_null($location_data) || $location_data === '' || $location_id < 1) {
            return false;
        }

        switch (substr($location_data, 0, 1)) {
            case 'g':
                try {
                    $group = OSC::model('core/country_group')->load($location_id);

                    if ($group instanceof Model_Core_Country_Group) {
                        $result = true;
                    }
                } catch (Exception $ex) {
                    $result = false;
                }
                break;
            case 'c':
                try {
                    $country = OSC::model('core/country_country')->load($location_id);

                    if ($country instanceof Model_Core_Country_Country) {
                        $result = true;
                    }
                } catch (Exception $ex) {
                    $result = false;
                }
                break;
            case 'p':
                try {
                    $province = OSC::model('core/country_province')->load($location_id);

                    if ($province instanceof Model_Core_Country_Province) {
                        $result = true;
                    }
                } catch (Exception $ex) {
                    $result = false;
                }
                break;
            default:
                $result = false;
                break;
        }

        return $result;
    }
}
