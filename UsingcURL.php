<?php 

	
	/**
    * 杨启华写于2016-4-29 17:09:31
    * 使用cURL函数库抓取http://news.dbanotes.net/这个页面的所有文章标题和链接
    * 这里有点大材小用
    */
    class WebCollection 
    {
    	private $filename;       //要存入内容的txt文件的路径
    	private $url;            //要抓取的初始页面的url，这里为"http://news.dbanotes.net/"
    	private $titleandlink=array();    //抓取到的内容
    	private $title=array();  //将抓取到的文章题目放入这个数组中
    	private $link=array();   //将抓取到的文章题目放入这个数组中
    	private $pages=array();  // 将所有要抓取的页面的url放入这个数组中。
    	private $allcontent="";     //将抓取到的所有标题和链接放入这个变量中去
    	function __construct($filename,$url)
    	{
    		$this->filename=$filename;
    		$this->url=$url;
    	}
    	//获得所有题目和链接
    	function getAllTitleAndLink(){
    		$title_count=0;
    		$link_count=0;
    		for ($i=0; $i <count($this->pages) ; $i++) { 
                $url = $i==0 ? $this->url : $this->url."x";
                $fcontent=$this->get_web_page($url,$this->pages[$i]);
				$pat="/<td class=\"title\"><a target=\"_blank\" href=\"([^\"]*)\"[^>]*>([^<]*)<\/a>/";
				preg_match_all($pat,$fcontent,$this->titleandlink);
				//将抓取到的所有题目存入到数组中
				for ($k=0; $k < count($this->titleandlink[1]); $k++) { 
					$this->link[$link_count]=$this->titleandlink[1][$k];
					$link_count++;
				}
				//将抓取到的所有链接存入到数组中
				for ($k=0; $k < count($this->titleandlink[2]); $k++) { 
					$this->title[$title_count]=$this->titleandlink[2][$k];
					$title_count++;
				}
    		}	    		
    	}
    	//获得所有页面的链接
    	function getAllPages(){
            //第一页可以请求http://news.dbanotes.net/ 参数设为fnid=Ua5Htl92ld
            $this->pages[0]="fnid=Ua5Htl92ld";
            $fcontent = $this->get_web_page($this->url,$this->pages[0]);
    		$pat_page="/<a href=\"([^\"]*)\"[^>]*>More<\/a>/";//利用正则表达式获得More的链接
    		$content=array();

    		for($i=1;preg_match_all($pat_page,$fcontent,$content)!=0;$i++) {  //直到所有的More链接遍历完成
    			$this->pages[$i]=substr($content[1][0],3);   //将所有More链接存入到数组中
    			$fcontent=$this->get_web_page($this->url."x",$this->pages[$i]);
    		}

    	}

    	//将抓取到的所有题目和链接写入到txt文件中
    	function writeTotxt(){
    		for ($i=0; $i <count($this->title) ; $i++) { 
    			$this->allcontent=$this->allcontent.($i+1).".".$this->title[$i]."\n".$this->link[$i]."\n\n";
    		}
    		$fp = fopen($this->filename, 'w')or die("打开文件失败"); //以写的方式打开该文件，不存在则创建它。
    		//将内容写入文件
    		if(fwrite($fp,$this->allcontent)or die("写入失败")){
    			echo "提示：所有文章题目和链接已写入同级目录下的".$this->filename;
    		} 
            fclose($fp);                       //关闭句柄
    	}

    	//使用cURL库获取指定web服务器内容，有很好的扩展性。
    	function get_web_page($url,$curl_data) 
		{ 
		    $options = array( 
		        CURLOPT_RETURNTRANSFER => true,         // 获取的信息以文件流的形式返回，而不是直接输出 
		        CURLOPT_HEADER         => false,        // 不将头文件的信息作为数据流返回
		        CURLOPT_FOLLOWLOCATION => true,         // 将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量 
		        CURLOPT_ENCODING       => "",           // HTTP请求头中"Accept-Encoding: "的值。支持的编码有"identity"，"deflate"和"gzip"。如果为空字符串""，请求头会发送所有支持的编码类型
		        // 模仿那种浏览器进行访问 
		        CURLOPT_USERAGENT      => "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)",     
		        CURLOPT_AUTOREFERER    => true,         // 当根据Location:重定向时，自动设置header中的Referer:信息
		        CURLOPT_CONNECTTIMEOUT => 120,          // 连接时的超时时间
		        CURLOPT_TIMEOUT        => 120,          // 响应时的超时时间 
		        CURLOPT_MAXREDIRS      => 10,           // 最大的重定向次数
		        CURLOPT_POST           => 1,            // 发送一个常规的POST请求，像提交表单一样
		        CURLOPT_POSTFIELDS     => $curl_data,   // 全部数据使用HTTP协议中的"POST"操作来发送，这个参数可以通过urlencoded后的字符串类似'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组 
		        CURLOPT_SSL_VERIFYHOST => 0,            // 不检查SSL加密算法是否存在
		        CURLOPT_SSL_VERIFYPEER => false,        // 不对认证证书来源的检查
		        // CURLOPT_FILE           => $fp			//将返回的文件流写入指定的文件,默认为STDOUT (浏览器)。
		    ); 

		    $ch      = curl_init($url); 
		    curl_setopt_array($ch,$options); 
		    $content = curl_exec($ch); 
		    $err     = curl_errno($ch); 
		    $errmsg  = curl_error($ch); 
		    $header  = curl_getinfo($ch); 
		    // fclose($fp);
		    curl_close($ch); 
		    return $content; //若没有定义CURLOPT_FILE，则返回内容，若有定义，则返回1表示内容写入文件成功。
		}
    }

    $web=new WebCollection("web.txt","http://news.dbanotes.net/");
    $web->getAllPages();
    $web->getAllTitleAndLink();
    $web->writeTotxt();
	 
 ?>