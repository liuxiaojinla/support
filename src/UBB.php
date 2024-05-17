<?php

namespace Xin\Support;

final class UBB
{
	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @param string $content
	 */
	public function __construct($content)
	{
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->parse();
	}

	/**
	 * 解析UBB语法
	 * @return string
	 */
	public function parse()
	{
		$Text = trim($this->content);
		$Text = htmlspecialchars($Text);
		$Text = preg_replace("/\\t/is", "  ", $Text);
		$Text = preg_replace("/\\[h1\\](.+?)\\[\\/h1\\]/is", "<h1>\\1</h1>", $Text);
		$Text = preg_replace("/\\[h2\\](.+?)\\[\\/h2\\]/is", "<h2>\\1</h2>", $Text);
		$Text = preg_replace("/\\[h3\\](.+?)\\[\\/h3\\]/is", "<h3>\\1</h3>", $Text);
		$Text = preg_replace("/\\[h4\\](.+?)\\[\\/h4\\]/is", "<h4>\\1</h4>", $Text);
		$Text = preg_replace("/\\[h5\\](.+?)\\[\\/h5\\]/is", "<h5>\\1</h5>", $Text);
		$Text = preg_replace("/\\[h6\\](.+?)\\[\\/h6\\]/is", "<h6>\\1</h6>", $Text);
		$Text = preg_replace("/\\[separator\\]/is", "", $Text);
		$Text = preg_replace("/\\[center\\](.+?)\\[\\/center\\]/is", "<span style=\"text-align: center\">\\1</span>",
			$Text);
		$Text = preg_replace("/\\[url=http:\\/\\/([^\\[]*)\\](.+?)\\[\\/url\\]/is", "<a href=\"http://\\1\" target=\"_blank\">\\2</a>", $Text);
		$Text = preg_replace("/\\[url=([^\\[]*)\\](.+?)\\[\\/url\\]/is", "<a href=\"http://\\1\" target=\"_blank\">\\2</a>", $Text);
		$Text = preg_replace("/\\[url\\]http:\\/\\/([^\\[]*)\\[\\/url\\]/is", "<a href=\"http://\\1\" target=\"_blank\">\\1</a>", $Text);
		$Text = preg_replace("/\\[url\\]([^\\[]*)\\[\\/url\\]/is", "<a href=\"\\1\" target=\"_blank\">\\1</a>", $Text);
		$Text = preg_replace("/\\[img\\](.+?)\\[\\/img\\]/is", "<img src=\"\\1\">", $Text);
		$Text = preg_replace("/\\[color=(.+?)\\](.+?)\\[\\/color\\]/is", "<span style=\"color: \\1\">\\2</span>", $Text);
		$Text = preg_replace("/\\[size=(.+?)\\](.+?)\\[\\/size\\]/is", "<span style=\"font-size: \\1\">\\2</span>", $Text);
		$Text = preg_replace("/\\[sup\\](.+?)\\[\\/sup\\]/is", "<sup>\\1</sup>", $Text);
		$Text = preg_replace("/\\[sub\\](.+?)\\[\\/sub\\]/is", "<sub>\\1</sub>", $Text);
		$Text = preg_replace("/\\[pre\\](.+?)\\[\\/pre\\]/is", "<pre>\\1</pre>", $Text);
		$Text = preg_replace("/\\[email\\](.+?)\\[\\/email\\]/is", "<a href=\"mailto:\\1\">\\1</a>", $Text);
		$Text = preg_replace("/\\[colorTxt\\](.+?)\\[\\/colorTxt\\]/eis", "color_txt('\\1')", $Text);
		$Text = preg_replace("/\\[emot\\](.+?)\\[\\/emot\\]/eis", "emot('\\1')", $Text);
		$Text = preg_replace("/\\[i\\](.+?)\\[\\/i\\]/is", "<i>\\1</i>", $Text);
		$Text = preg_replace("/\\[u\\](.+?)\\[\\/u\\]/is", "<u>\\1</u>", $Text);
		$Text = preg_replace("/\\[b\\](.+?)\\[\\/b\\]/is", "<b>\\1</b>", $Text);
		$Text = preg_replace("/\\[quote\\](.+?)\\[\\/quote\\]/is", " <div class=\"quote\"><h5>引用:</h5><blockquote>\\1</blockquote></div>", $Text);
		$Text = preg_replace("/\\[code\\](.+?)\\[\\/code\\]/eis", "highlight_code('\\1')", $Text);
		$Text = preg_replace("/\\[php\\](.+?)\\[\\/php\\]/eis", "highlight_code('\\1')", $Text);
		$Text = preg_replace("/\\[sig\\](.+?)\\[\\/sig\\]/is", "<div class=\"sign\">\\1</div>", $Text);
		$Text = preg_replace("/\\n/is", "<br/>", $Text);

		return $Text;
	}

}
