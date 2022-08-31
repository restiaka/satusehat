<?php
define('WS_FHIR', 'on');
define('WS_FHIR_API', "https://api-satusehat-dev.dto.kemkes.go.id/fhir-r4/v1/");
define('WS_FHIR_AUTH_API', "https://api-satusehat-dev.dto.kemkes.go.id/oauth2/v1/");
define('WS_FHIR_CUST_ID', "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"); /*isi dengan client_id*/
define('WS_FHIR_CUST_KEY', "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"); /*isi dengan client_secret*/

function generateHeaderFHIR($params = array()) {
	$header_with_token = array('Content-Type' => 'application/json');

	if (defined('WS_FHIR') && WS_FHIR != 'off') {
		if (defined('WS_FHIR_AUTH_API')) {
			$urlWS = WS_FHIR_AUTH_API.'accesstoken?grant_type=client_credentials';
			$custId = WS_FHIR_CUST_ID;
			$custKey = WS_FHIR_CUST_KEY;
			try {
				$isUp = @get_headers($urlWS, 1);
				if ($isUp) {
					//  Initiate curl
					$ch = curl_init();

					// Attach encoded JSON string to the POST fields
					curl_setopt($ch, CURLOPT_POSTFIELDS,"client_id=$custId&client_secret=$custKey");

					// Set The Response Format to Json
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

					// Disable SSL verification
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

					// Will return the response, if false it print the response
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

					// Set the url
					curl_setopt($ch, CURLOPT_URL,$urlWS);

					// Execute
					$result=curl_exec($ch);

					// Closing
					curl_close($ch);

					if (!empty($result)) {
						$response = json_decode($result);
						$header_with_token['Authorization'] = 'Bearer ' . $response->access_token;
					}
				}
			} catch (Exception $e) {
				// $ret['msg'] = $e->getMessage();
			}
		}
	}
	return $header_with_token;
}

function fhirPasienByNIK($params = array()) {
	$ret = array (
		'status' => 0,
		'msg' => '',
		'data' => array(),
	);

	if (defined('WS_FHIR') && WS_FHIR != 'off') {
		if (!empty($params['nik'])) {
			if (defined('WS_FHIR_API')) {
				$urlWS = WS_FHIR_API."Patient?identifier=https://fhir.kemkes.go.id/id/nik|".$params['nik'];
				try {
					$isUp = @get_headers($urlWS, 1);
					if ($isUp) {
						$header = generateHeaderFHIR();

						//  Initiate curl
						$ch = curl_init();

						// Set The Response Format to Json
						curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: '.$header['Authorization']));

						// Disable SSL verification
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

						// Will return the response, if false it print the response
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

						// Set the url
						curl_setopt($ch, CURLOPT_URL,$urlWS);

						// Execute
						$result=curl_exec($ch);

						// Closing
						curl_close($ch);
						if (!empty($result)) {
							$ret['status'] = 1;
							$response = json_decode($result);
							$ret['data'] = $response;
						} else {
							$ret['msg'] = 'Tidak dapat response dari server FHIR! (fhir)';
						}
					} else {
						$ret['msg'] = 'Tidak terkoneksi ke service FHIR! (fhir)';
					}
					
				} catch (Exception $e) {
					$ret['msg'] = $e->getMessage();
				}
			}
		} else {
			$ret['msg'] = 'parameter NIK kosong';
		}
	} else {
		$ret['status'] = 1;
		$ret['msg'] = 'Fitur WS FHIR disabled!';
	}
	
	if(!empty($params['output_type']) && $params['output_type'] == 'echo') {
		echo json_encode($ret);
	}
	else {
		return $ret;
	}
}

function fhirBundle($params) {
	$ret = array (
		'status' => 0,
		'msg' => '',
		'data' => array(),
	);

	if (defined('WS_FHIR_API')) {
		$urlWS = WS_FHIR_API;
		try {
			$isUp = @get_headers($urlWS, 1);
			if ($isUp) {
				$header = generateHeaderFHIR();
				$payload = json_encode($params);
				//  Initiate curl
				$ch = curl_init();

				// Attach encoded JSON string to the POST fields
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

				// Set The Response Format to Json
				curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Authorization: '.$header['Authorization']));

				// Disable SSL verification
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

				// Will return the response, if false it print the response
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				// Set the url
				curl_setopt($ch, CURLOPT_URL,$urlWS);

				// Execute
				$result=curl_exec($ch);

				// Closing
				curl_close($ch);
				if (!empty($result)) {
					$ret['status'] = 1;
					$response = json_decode($result);
					$ret['data'] = $response;
				} else {
					$ret['msg'] = 'Tidak dapat response dari server FHIR! (fhir)';
				}
			} else {
				$ret['msg'] = 'Tidak terkoneksi ke service FHIR! (fhir)';
			}
			
		} catch (Exception $e) {
			$ret['msg'] = $e->getMessage();
		}
	}
	return $ret;
}

$nik = '3171022809990001';
$data = fhirPasienByNIK(array('nik'=>$nik));
echo "<h1>Response Data Pasien NIK $nik</h1>";
echo '<pre>';
print_r($data);
echo '</pre>';

/*
Seorang anak berusia 2 tahun dengan KIA 3171022809990001  pada tanggal 13 Agustus  2022 datang ke RS XXX ke poliklinik anak dengan dr.XXX Sp.A dengan NIK 367400001111223. Pasien mengeluhkan sering mencret setiap minum susu formula. Keluhan perdarahan dari dubur tidak ada. 
Hasil pemeriksaan fisik 
Tekanan darah : 90/60 mmHg (normal)
Denyut nadi : 110 x/min (normal)
Lanju Pernapasan : 24x/min (normal)
Suhu : 38,7 C (febris)
Prosedur : 89.03 Interview and evaluation, described as comprehensive
Berdasarkan hasil pemeriksaan yang telah dilakukan, dokter mendiagnosis pasien dengan lactose intolerance (E73. 9 for Lactose intolerance, unspecified)
*/

$id_organization = 'xxxxxxxx'; /*ubah dengan id organisasi*/

$id_patient = '100000030009';
$nama_patient = 'Budi Santoso MSc';

$id_practitioner = 'N10000002';
$nama_practitioner = 'Dr. dr. Voigt MARS.';

$id_location = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'; /*isi dengan id location yang sudah dibuat*/
$nama_location = 'Poli Penyakit Dalam';

$identifier_encounter = 'K0003';

$uuid_encounter = '588744a1-b657-40e5-ad1c-e1978ed9ceb7';
$date = date('Y-m-d');
$period_start = $date."T07:00:00+07:00";
$period_in_progress_1 = $date."T08:00:00+07:00";
$period_end = $date."T09:00:00+07:00";

$array_bundle = array(
	"resourceType" => "Bundle",
	"type" => "transaction",
	"entry" => array(

//encounter
		array(
			"fullUrl" => "urn:uuid:$uuid_encounter",
			"resource" => array(
				"resourceType" => "Encounter",
				"identifier" => array(
					array(
						"system" => "http://sys-ids.kemkes.go.id/encounter/$id_organization",
						"value" => "$identifier_encounter"
					)
				),
				"status" => "finished",
				"class" => array(
					"system" => "http://terminology.hl7.org/CodeSystem/v3-ActCode",
					"code" => "AMB",
					"display" => "ambulatory"
				),
				"subject" => array(
					"reference" => "Patient/$id_patient",
					"display" => "$nama_patient"
				),
				"participant" => array(
					array(
						"type" => array(
							array(
								"coding" => array(
									array(
										"system" => "http://terminology.hl7.org/CodeSystem/v3-ParticipationType",
										"code" => "ATND",
										"display" => "attender"
									)
								)
							)
						),
						"individual" => array(
							"reference" => "Practitioner/$id_practitioner",
							"display" => "$nama_practitioner"
						)
					)
				),
				"period" => array(
					"start" => "$period_start",
					"end" => "$period_end"
				),
				"location" => array(
					array(
						"location" => array(
							"reference" => "Location/$id_location",
							"display" => "$nama_location"
						)
					)
				),
				"diagnosis" => array(
					array(
						"condition" => array(
							"reference" => "urn:uuid:c820f626-0dfd-4a9b-acda-5b8d526429f6",
							"display" => "Lactose intolerance, unspecified"
						),
						"use" => array(
							"coding" => array(
								array(
									"system" => "http://terminology.hl7.org/CodeSystem/diagnosis-role",
									"code" => "DD",
									"display" => "Discharge diagnosis"
								)
							)
						),
						"rank" => 1
					)
				),
				"statusHistory" => array(
					array(
						"status" => "arrived",
						"period" => array(
							"start" => "$period_start",
							"end" => "$period_in_progress_1"
						)
					),
					array(
						"status" => "in-progress",
						"period" => array(
							"start" => "$period_in_progress_1",
							"end" => "$period_end"
						)
					),
					array(
						"status" => "finished",
						"period" => array(
							"start" => "$period_end",
							"end" => "$period_end"
						)
					)
				),
				"serviceProvider" => array(
					"reference" => "Organization/$id_organization"
				)
			),
			"request" => array(
				"method" => "POST",
				"url" => "Encounter"
			)
		),

//observation
		array(
			"fullUrl" => "urn:uuid:39ada41c-dc1b-4a71-9c59-778b6c1503d3",
			"resource" => array(
				"resourceType" => "Observation",
				"status" => "final",
				"category" => array(
					array(
						"coding" => array(
							array(
								"system" => "http://terminology.hl7.org/CodeSystem/observation-category",
								"code" => "vital-signs",
								"display" => "Vital Signs"
							)
						)
					)
				),
				"code" => array(
					"coding" => array(
						array(
							"system" => "http://loinc.org",
							"code" => "8867-4",
							"display" => "Heart rate"
						)
					)
				),
				"subject" => array(
					"reference" => "Patient/$id_patient"
				),
				"encounter" => array(
					"reference" => "urn:uuid:$uuid_encounter",
					"display" => "Pemeriksaan Fisik Nadi $nama_patient"
				),
				"effectiveDateTime" => "2022-07-14",
				"valueQuantity" => array(
					"value" => 110,
					"unit" => "beats/minute",
					"system" => "http://unitsofmeasure.org",
					"code" => "/min"
				)
			),
			"request" => array(
				"method" => "POST",
				"url" => "Observation"
			)
		),
		array(
			"fullUrl" => "urn:uuid:ecdf0cfc-8c42-4940-b4bf-83ceb6168bb8",
			"resource" => array(
				"resourceType" => "Observation",
				"status" => "final",
				"category" => array(
					array(
						"coding" => array(
							array(
								"system" => "http://terminology.hl7.org/CodeSystem/observation-category",
								"code" => "vital-signs",
								"display" => "Vital Signs"
							)
						)
					)
				),
				"code" => array(
					"coding" => array(
						array(
							"system" => "http://loinc.org",
							"code" => "9279-1",
							"display" => "Respiratory rate"
						)
					)
				),
				"subject" => array(
					"reference" => "Patient/$id_patient"
				),
				"encounter" => array(
					"reference" => "urn:uuid:$uuid_encounter",
					"display" => "Pemeriksaan Fisik Pernafasan $nama_patient"
				),
				"effectiveDateTime" => "2022-07-14",
				"valueQuantity" => array(
					"value" => 24,
					"unit" => "breaths/minute",
					"system" => "http://unitsofmeasure.org",
					"code" => "/min"
				)
			),
			"request" => array(
				"method" => "POST",
				"url" => "Observation"
			)
		),
		array(
			"fullUrl" => "urn:uuid:b9e2118a-f966-4218-8245-801ab91b6c87",
			"resource" => array(
				"resourceType" => "Observation",
				"status" => "final",
				"category" => array(
					array(
						"coding" => array(
							array(
								"system" => "http://terminology.hl7.org/CodeSystem/observation-category",
								"code" => "vital-signs",
								"display" => "Vital Signs"
							)
						)
					)
				),
				"code" => array(
					"coding" => array(
						array(
							"system" => "http://loinc.org",
							"code" => "8480-6",
							"display" => "Systolic blood pressure"
						)
					)
				),
				"subject" => array(
					"reference" => "Patient/$id_patient"
				),
				"encounter" => array(
					"reference" => "urn:uuid:$uuid_encounter",
					"display" => "Pemeriksaan Fisik Sistolik $nama_patient"
				),
				"effectiveDateTime" => "2022-07-14",
				"bodySite" => array(
					"coding" => array(
						array(
							"system" => "http://snomed.info/sct",
							"code" => "368209003",
							"display" => "Right arm"
						)
					)
				),
				"valueQuantity" => array(
					"value" => 90,
					"unit" => "mmarray(Hg)",
					"system" => "http://unitsofmeasure.org",
					"code" => "mmarray(Hg)"
				),
				"interpretation" => array(
					array(
						"coding" => array(
							array(
								"system" => "http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation",
								"code" => "N",
								"display" => "Normal"
							)
						),
						"text" => "Normal"
					)
				)
			),
			"request" => array(
				"method" => "POST",
				"url" => "Observation"
			)
		),
		array(
			"fullUrl" => "urn:uuid:a3db0a6a-045e-4877-8742-ffeec1c7790f",
			"resource" => array(
				"resourceType" => "Observation",
				"status" => "final",
				"category" => array(
					array(
						"coding" => array(
							array(
								"system" => "http://terminology.hl7.org/CodeSystem/observation-category",
								"code" => "vital-signs",
								"display" => "Vital Signs"
							)
						)
					)
				),
				"code" => array(
					"coding" => array(
						array(
							"system" => "http://loinc.org",
							"code" => "8462-4",
							"display" => "Diastolic blood pressure"
						)
					)
				),
				"subject" => array(
					"reference" => "Patient/$id_patient",
					"display" => "$nama_patient"
				),
				"encounter" => array(
					"reference" => "urn:uuid:$uuid_encounter",
					"display" => "Pemeriksaan Fisik Diastolik $nama_patient"
				),
				"effectiveDateTime" => "2022-07-14",
				"bodySite" => array(
					"coding" => array(
						array(
							"system" => "http://snomed.info/sct",
							"code" => "368209003",
							"display" => "Right arm"
						)
					)
				),
				"valueQuantity" => array(
					"value" => 60,
					"unit" => "mmarray(Hg)",
					"system" => "http://unitsofmeasure.org",
					"code" => "mmarray(Hg)"
				),
				"interpretation" => array(
					array(
						"coding" => array(
							array(
								"system" => "http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation",
								"code" => "N",
								"display" => "Normal"
							)
						),
						"text" => "Normal"
					)
				)
			)
			,
			"request" => array(
				"method" => "POST",
				"url" => "Observation"
			)
		),
		array(
			"fullUrl" => "urn:uuid:d02c7156-54c8-46c1-b00a-d727647825a3",
			"resource" => array(
				"resourceType" => "Observation",
				"status" => "final",
				"category" => array(
					array(
						"coding" => array(
							array(
								"system" => "http://terminology.hl7.org/CodeSystem/observation-category",
								"code" => "vital-signs",
								"display" => "Vital Signs"
							)
						)
					)
				),
				"code" => array(
					"coding" => array(
						array(
							"system" => "http://loinc.org",
							"code" => "8310-5",
							"display" => "Body temperature"
						)
					)
				),
				"subject" => array(
					"reference" => "Patient/$id_patient"
				),
				"encounter" => array(
					"reference" => "urn:uuid:$uuid_encounter",
					"display" => "Pemeriksaan Fisik Suhu $nama_patient"
				),
				"effectiveDateTime" => "2022-07-14",
				"valueQuantity" => array(
					"value" => 38.7,
					"unit" => "C",
					"system" => "http://unitsofmeasure.org",
					"code" => "Cel"
				),
				"interpretation" => array(
					array(
						"coding" => array(
							array(
								"system" => "http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation",
								"code" => "H",
								"display" => "High"
							)
						),
						"text" => "Di atas nilai referensi (Febris)"
					)
				)
			),
			"request" => array(
				"method" => "POST",
				"url" => "Observation"
			)
		),

//condition
		array(
			"fullUrl" => "urn:uuid:c820f626-0dfd-4a9b-acda-5b8d526429f6",
			"resource" => array(
				"resourceType" => "Condition",
				"clinicalStatus" => array(
					"coding" => array(
						array(
							"system" => "http://terminology.hl7.org/CodeSystem/condition-clinical",
							"code" => "active",
							"display" => "Active"
						)
					)
				),
				"category" => array(
					array(
						"coding" => array(
							array(
								"system" => "http://terminology.hl7.org/CodeSystem/condition-category",
								"code" => "encounter-diagnosis",
								"display" => "Encounter Diagnosis"
							)
						)
					)
				),
				"code" => array(
					"coding" => array(
						array(
							"system" => "http://hl7.org/fhir/sid/icd-10",
							"code" => "E73.9",
							"display" => "Lactose intolerance, unspecified"
						)
					)
				),
				"subject" => array(
					"reference" => "Patient/$id_patient",
					"display" => "$nama_patient"
				),
				"encounter" => array(
					"reference" => "urn:uuid:$uuid_encounter"
				),
				"onsetDateTime" => "$date",
				"recordedDate" => "$date"
			),
			"request" => array(
				"method" => "POST",
				"url" => "Condition"
			)
		),

//procedure
		array(
			"fullUrl" => "urn:uuid:fb7d9e9d-2068-42f7-9af5-d9b18226b4c0",
			"resource" => array(
				"resourceType" => "Procedure",
				"status" => "completed",
				"category" => array(
					"coding" => array(
						array(
							"system" => "http://snomed.info/sct",
							"code" => "103693007",
							"display" => "Diagnostic procedure"
						)
					),
					"text" => "Diagnostic procedure"
				),
				"code" => array(
					"coding" => array(
						array(
							"system" => "http://hl7.org/fhir/sid/icd-9-cm",
							"code" => "89.03",
							"display" => "Interview and evaluation, described as comprehensive"
						)
					)
				),
				"subject" => array(
					"reference" => "Patient/$id_patient",
					"display" => "$nama_patient"
				),
				"encounter" => array(
					"reference" => "urn:uuid:$uuid_encounter",
					"display" => "Interview pasien atas nama $nama_patient"
				),
				"performedPeriod" => array(
					"start" => $date."T13:31:00+01:00",
					"end" => $date."T14:27:00+01:00"
				),
				"performer" => array(
					array(
						"actor" => array(
							"reference" => "Practitioner/$id_practitioner",
							"display" => "$nama_practitioner"
						)
					)
				),
				"reasonCode" => array(
					array(
						"coding" => array(
							array(
								"system" => "http://hl7.org/fhir/sid/icd-10",
								"code" => "E73.9",
								"display" => "Lactose intolerance, unspecified"
							)
						)
					)
				),
				// "bodySite" => array(
				// 	array(
				// 		"coding" => array(
				// 			array(
				// 				"system" => "http://snomed.info/sct",
				// 				"code" => "302551006",
				// 				"display" => "Entire Thorax"
				// 			)
				// 		)
				// 	)
				// ),
				"note" => array(
					array(
						"text" => "Pasien mengeluhkan sering mencret setiap minum susu formula. Keluhan perdarahan dari dubur tidak ada."
					)
				)
			),
			"request" => array(
				"method" => "POST",
				"url" => "Procedure"
			)
		)
	)
);

echo '<h1>JSON Bundle yang dikirim</h1>';
echo '<pre>';
echo json_encode($array_bundle, JSON_PRETTY_PRINT);
echo '</pre>';

echo '<h1>Response pengiriman Bundle</h1>';
$response_bundle = fhirBundle($array_bundle);
echo '<pre>';
print_r($response_bundle);
echo '</pre>';