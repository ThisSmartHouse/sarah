<?php

namespace App\Http\Controllers\MachineLearning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Library\Iterators\RandomIterator;
class ClassifyBucketController extends Controller
{
    public function view(Request $request, $bucket) 
    {
        $s3 = \Storage::disk($bucket)->getDriver()->getAdapter()->getClient();
        
        $result = $s3->listObjects(['Bucket' => $bucket]);
        
        $current = $result['Contents'][array_rand($result['Contents'], 1)];
        
        $cmd = $s3->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $current['Key'],
            'ContentType' => 'image/jpeg'
        ]);
        
        $request = $s3->createPresignedRequest($cmd, '+30 minutes');
        
        return view('ml.classify', [
            'image_url' => (string)$request->getUri(),
            'bucket' => $bucket,
            'image_key' => $current['Key']
        ]);
    }
    
    public function submit(Request $request, $bucket)
    {
        $s3 = \Storage::disk($bucket)->getDriver()->getAdapter()->getClient();
        
        $this->validate($request, [
            'image_key' => 'required|min:1',
            'image_result' => 'required|boolean',
        ]);
        
        switch($request->get('image_result')) {
            case 1:
                $destBucket = "{$bucket}-positive";
                break;
            case 0:
                $destBucket = "{$bucket}-negative";
                break;
            default:
                throw new \Exception("Invalid Result");
        }
        
        $s3->copyObject([
            'Bucket' => $destBucket,
            'Key' => $request->get('image_key'),
            'CopySource' => $bucket . '/' . $request->get('image_key')
        ]);
        
        \Storage::disk($bucket)->delete($request->get('image_key'));
        
        return redirect()->route('ml.classify.view', ['bucket' => $bucket]);
    }
    
}