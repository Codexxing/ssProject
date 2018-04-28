<?php
/**
 * ZHIMA API: zhima.merchant.data.upload.initialize request
 *
 * @author auto create
 * @since 1.0, 2018-01-02 15:10:29
 */
class ZhimaMerchantDataUploadInitializeRequest
{
	/** 
	 * 数据应用的场景编码，场景码和场景名称（数字或字符串为场景码）如下：
8：数据反馈
32：骑行
CAR_RENTING：租车行业解决方案
	 **/
	private $sceneCode;

	private $apiParas = array();
	private $fileParas = array();
	private $apiVersion="1.0";
	private $scene;
	private $channel;
	private $platform;
	private $extParams;

	
	public function setSceneCode($sceneCode)
	{
		$this->sceneCode = $sceneCode;
		$this->apiParas["scene_code"] = $sceneCode;
	}

	public function getSceneCode()
	{
		return $this->sceneCode;
	}

	public function getApiMethodName()
	{
		return "zhima.merchant.data.upload.initialize";
	}

	public function setScene($scene)
	{
		$this->scene=$scene;
	}

	public function getScene()
	{
		return $this->scene;
	}
	
	public function setChannel($channel)
	{
		$this->channel=$channel;
	}

	public function getChannel()
	{
		return $this->channel;
	}
	
	public function setPlatform($platform)
	{
		$this->platform=$platform;
	}

	public function getPlatform()
	{
		return $this->platform;
	}

	public function setExtParams($extParams)
	{
		$this->extParams=$extParams;
	}

	public function getExtParams()
	{
		return $this->extParams;
	}	

	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function getFileParas()
	{
		return $this->fileParas;
	}

	public function setApiVersion($apiVersion)
	{
		$this->apiVersion=$apiVersion;
	}

	public function getApiVersion()
	{
		return $this->apiVersion;
	}

}
