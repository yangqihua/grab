<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>网络爬虫</title>
</head>
<body>
	<?php
	    /**
	    * 杨启华写于2015-5-30
	    */
	    class WebCollection 
	    {
	    	private $filename;       //要存入内容的txt文件的路径
	    	private $url;            //要抓取的初始页面的url，这里为"http://news.dbanotes.net/"
	    	private $titleandlink=array();    //抓取到的内容
	    	private $title=array();  //将抓取到的文章题目放入这个数组中
	    	private $link=array();   //将抓取到的文章题目放入这个数组中
	    	private $pages=array();  // 将所有要抓取的页面的url放入这个数组中。
	    	private $allcontent;     //将抓取到的所有标题和链接放入这个变量中去
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
		    		$fp=@fopen($this->pages[$i], "r") or die("超时");
					$fcontent=file_get_contents($this->pages[$i]);
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
	    		$fp=@fopen($this->url, "r") or die("超时");
	    		$fcontent=file_get_contents($this->url);
	    		$pat_page="/<a href=\"([^\"]*)\"[^>]*>More<\/a>/";//利用正则表达式获得More的链接
	    		$content=array();
	    		$this->pages[0]=$this->url;
	    		for($i=1;preg_match_all($pat_page,$fcontent,$content)!=0;$i++) {  //直到所有的More链接遍历完成
	    			$this->pages[$i]="http://news.dbanotes.net".$content[1][0];   //将所有More链接存入到数组中
	    			$fcontent=file_get_contents($this->pages[$i]);
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
	    }

	    $web=new WebCollection("web.txt","http://news.dbanotes.net/");
	    $web->getAllPages();
	    $web->getAllTitleAndLink();
	    $web->writeTotxt();
	?>
</body>
</html>