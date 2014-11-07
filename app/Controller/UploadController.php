<?php
namespace App\Controller;

class UploadController extends BaseController
{
	public function options()
	{
		// $this->app->response(200);
	}

	public function upload()
	{
        $upload_path = UPLOAD_PATH;
        $upload_url  = UPLOAD_URL;

        $files = $_FILES['image'];

        if( !isset($files) ) {
        	$this->throwInternalError('Image not found');
        }

        $filename = $files['name'];
        $fileImg  = $files['tmp_name'];
        $fileType = $files['type'];

        $paths = pathinfo($filename);
        if(array_key_exists('extension', $paths)) {
            $extension = $paths['extension'];
        } else {
            switch ($fileType) { 
                case 'image/jpeg': 
                    $extension = 'jpg';
                    break;
                case 'image/png': 
                    $extension = 'png';
                    break; 
                default:
                    $this->throwInternalError('Unsopported image file!! (jpg,png)');
                    break;
            } 
        }

        $id     = isset($_REQUEST['id'])   ? $_REQUEST['id'] : null ;
        $name   = isset($_REQUEST['name'])   ? $_REQUEST['name'] : $paths['filename'] . '.' . $extension ;
        $width  = isset($_REQUEST['width'])  ? $_REQUEST['width']  : null ;
        $height = isset($_REQUEST['height']) ? $_REQUEST['height'] : null ;
        $ratio  = isset($_REQUEST['ratio'])  ? (boolean) $_REQUEST['ratio'] : false ;
        $unique = isset($_REQUEST['unique']) ? $_REQUEST['unique'] : null ;

        if( $id ) {
            if( $id !== 'avatar' ) {
                $id = md5($id);
                $upload_path .= '/' . $id;
                $upload_url  .= '/' . $id;
                if(!is_dir($upload_path)){
                    if(false === mkdir($upload_path, 0777, true)){
                        throw new Exception(sprintf('Unable to create the %s directory', $upload_path));
                    }
                }
            } else {
                $upload_path .= '/avatar';
                $upload_url  .= '/avatar';
            }
        }

        if( $unique ) {
            $name .= '_' . (count(glob($upload_path . "/" . $name ."_*.{jpg,jpeg,png}", GLOB_BRACE)) + 1);
        }

        // upload only
        if( empty($width) && empty($height) ) {
            $url      = $upload_url . DIRECTORY_SEPARATOR . $name;
            $target   = $upload_path . DIRECTORY_SEPARATOR . $name;
            $uploaded = move_uploaded_file($fileImg, $target);

            echo json_encode([
                'uploaded'  => $uploaded,
                'url'       => $url,
                'dataURI'   => static::dataURI($target, $fileType)
            ]);
            return;
        }

        // Upload & resize image
        $imagehand = new \App\Upload\Upload( $files );
        if ( $imagehand->uploaded ) {
            $imagehand->file_dst_name_ext  = $extension;
            $imagehand->file_new_name_body = $name;
            $imagehand->file_overwrite     = true;
            $imagehand->image_crop         = '107 193 917 574';
            // $imagehand->image_resize       = true;
            // if( $ratio && empty($width) ){
            //     $imagehand->image_y = $height;
            //     $imagehand->image_ratio_x = true;
            // } elseif( $ratio && empty($height) ){
            //     $imagehand->image_x = $width;
            //     $imagehand->image_ratio_y = true;
            // } else {
            //     $imagehand->image_x = $width;
            //     $imagehand->image_y = $height;
            //     $imagehand->image_ratio = $ratio;
            // }
            $imagehand->process($upload_path);
            if (!$imagehand->processed) {
                throw new Exception('error : ' . $imagehand->error);
            }

            $response = [
                'filename'  => $imagehand->file_dst_name,
                'width'     => $imagehand->image_dst_x,
                'height'    => $imagehand->image_dst_y,
                'url'       => $upload_url . '/' . $imagehand->file_dst_name,
                'dataURI'   => static::dataURI($imagehand->file_dst_pathname, $fileType)
            ];
            echo json_encode($response, JSON_PRETTY_PRINT);

        } else {
            $this->throwInternalError('Image not uploaded : ' . $imagehand->error);
        }
	}

	public static function dataURI($filePath, $fileType){
        $contents = file_get_contents($filePath);
        $base64   = base64_encode($contents);
        return "data:$fileType;base64,$base64";
    }
}