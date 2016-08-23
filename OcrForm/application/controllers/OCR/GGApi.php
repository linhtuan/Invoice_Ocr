<?php

include_once 'creds.php'; // Get $api_key
 

 function CallGGAPIForImage($pathFile)
 {
     $api_key = 'AIzaSyA45Lsa-rV6WdUI9FQMbHUT4eIZKzfWm6E';
     $cvurl = 'https://vision.googleapis.com/v1/images:annotate?key=' . $api_key;
     $type = 'TEXT_DETECTION';
      $data = file_get_contents($pathFile);
    //  echo $pathFile;
      $base64 = base64_encode($data); 
      str_replace("data:image/jpeg;base64,", "",$base64)	  ;
       //Create this JSON
         $request_json = '{
			  	"requests": [
					{
					  "image": {
					    "content":"' . $base64 . '"
					  },
					  "features": [
					      {
					      	"type": "' . $type . '",
						"maxResults": 200
					      }
					  ]
					}
				]
			}';

			//echo $request_json;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $cvurl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
            $json_response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			//echo curl_error($curl) . ' (' . curl_errno($curl) . ')';
            curl_close($curl);
			
            if ($status != 200) {
               die("Error: call to URL $cvurl failed with status $status, response $json_response, curl_error " . curl_error($curl) . ', curl_errno ' . curl_errno($curl));
            }
			
          return $json_response;
 }
   function CallGGAPIForPdf($pathFile,$pageNum)
    {
        $type = 'TEXT_DETECTION';
        //Check if file is pdf page
        if($pageNum>=0)
        {
            $base64 = Pdf2Image($pathFile, $pageNum);
        }
        else {
             $data = file_get_contents($pathFile);
             $base64 = base64_encode($data);    
        }
             
            //Create this JSON
         $request_json = '{
			  	"requests": [
					{
					  "image": {
					    "content":"' . $base64 . '"
					  },
					  "features": [
					      {
					      	"type": "' . $type . '",
							"maxResults": 200
					      }
					  ]
					}
				]
			}';

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $cvurl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
            $json_response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($status != 200) {
                die("Error: call to URL $cvurl failed with status $status, response $json_response, curl_error " . curl_error($curl) . ', curl_errno ' . curl_errno($curl));
            }
          return $json_response;
    }