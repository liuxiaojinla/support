<?php

namespace Xin\Support\Web;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMText;
use DOMXPath;
use LogicException;
use Xin\Support\Str;

/**
 * HTML 处理工具类
 * 提供HTML解析、标签/属性清理、空节点移除、格式压缩等功能
 */
class Html
{
	/**
	 * HTML文档加载标志
	 */
	public const HTML_FLAGS = LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD;

	/** 私有构造，禁止实例化 */
	private function __construct()
	{
	}

	/**
	 * 创建DOMDocument实例并配置
	 * @param string|DOMNode $html
	 * @return DOMDocument
	 */
	public static function document($html, string $encoding = 'UTF-8', int $flags = self::HTML_FLAGS, array $options = [])
	{
		$options = array_merge([
		], $options);

		if ($html instanceof DOMDocument) {
			return $html;
		} elseif ($html instanceof DOMNode) {
			$html = self::toHtmlString($html);
		}

		// // 1. 统一转成 UTF-8（无论原来是 GBK/Windows-1252/ISO-8859-1）
		// $enc = mb_detect_encoding($html, ['UTF-8', 'GBK', 'ISO-8859-1'], true);
		// $html = $enc === 'UTF-8' ? $html : mb_convert_encoding($html, 'UTF-8', $enc);
		//
		// // 2. 整个字节流做 NFC 正规化（组合字符 → 预组合）
		// if (class_exists('Normalizer')) {
		// 	$html = Normalizer::normalize($html, Normalizer::FORM_C);
		// }

		// 强制闭合一些 HTML 标签
		$html = preg_replace('/<source\b([^>]*)>/i', '<source $1/>', $html);

		// 3. 把真正的 \u00A0 换成普通空格
		// $html = str_replace("\u{00A0}", ' ', $html);

		// 4. 如果想把多个连续空格再压成一个
		// $html = preg_replace('/\s{2,}/u', ' ', $html);

		// 创建实例并统一配置
		$document = new DOMDocument();
		$document->preserveWhiteSpace = false; // 去掉纯空白节点
		$document->formatOutput = false; // 保存时无缩进（可后面再改）

		// 屏蔽 libxml 报错，但记得清缓冲区
		@libxml_disable_entity_loader(true);
		libxml_use_internal_errors(true);

		// 解决中文乱码：先拼 XML 头再加载
		$loaded = $document->loadHTML($html, $flags);
		// $loaded = $document->loadHTML('<?xml encoding="UTF-8">' . $html, self::HTML_FLAGS);
		if ($loaded === false) {
			$errs = libxml_get_errors();
			libxml_clear_errors();
			throw new LogicException('libXML load failed: ' . print_r($errs, true));
		}

		// 指定输出编码（保存阶段生效
		$document->encoding = $encoding;

		// 清掉本次累积的错误，防止污染下次调用
		libxml_clear_errors();

		return $document;
	}

	/**
	 * 创建XPath实例并配置
	 * @param DOMNode|DOMXPath $nodeOrXpath
	 * @return DOMXPath
	 */
	public static function xpath($nodeOrXpath)
	{
		if ($nodeOrXpath instanceof DOMXPath) {
			return $nodeOrXpath;
		}

		$nodeOrXpath = $nodeOrXpath instanceof DOMDocument ? $nodeOrXpath : $nodeOrXpath->ownerDocument;
		if (!$nodeOrXpath) {
			throw new LogicException('Detached or invalid DOMNode');
		}

		return new DOMXPath($nodeOrXpath);
	}

	/**
	 * 快速清理HTML
	 * 支持自定义清理规则，适用于大多数场景
	 *
	 * @param string|DOMNode $htmlStr 原始HTML内容
	 * @param array $options 清理选项
	 * @return string|DOMNode 清理后的HTML
	 */
	public static function clean($htmlStr, array $options = [], $toString = true)
	{
		$node = $htmlStr instanceof DOMNode ? $htmlStr : self::document($htmlStr);

		// 合并选项
		$options = array_merge([
			'remove_tags' => [
				'header', 'footer', 'nav', 'aside',
				'svg', 'noscript', 'iframe', 'frame',
				'advertisement', 'ad',
			],
			'allow_attributes' => [
				'id', 'name', 'src', 'href', 'alt',
				'data-*',
			],
			'remove_empty_text_nodes' => true,
			'remove_empty_nodes' => true,
			'remove_comments' => true,
			'remove_meta' => true,
			'remove_styles' => true,
			'remove_scripts' => true,
			'remove_hidden_elements' => true,
			'compress_whitespace' => true,
		], $options);

		// 移除HTML中的meta标签
		if ($options['remove_meta']) {
			self::removeMeta($node);
		}

		// 移除HTML中的样式
		if ($options['remove_styles']) {
			self::removeStyles($node);
		}

		// 移除HTML中的脚本
		if ($options['remove_scripts']) {
			self::removeScripts($node);
		}

		// 移除HTML中的标签
		if ($options['remove_tags']) {
			self::removeTags($node, $options['remove_tags']);
		}

		// 移除隐藏的元素
		if ($options['remove_hidden_elements']) {
			self::removeHiddenElements($node);
		}

		// 移除HTML中的空节点
		if ($options['remove_empty_nodes']) {
			self::removeEmptyNodes($node);
		}

		// 允许的属性
		if ($options['allow_attributes']) {
			self::removeAttributes($node, $options['allow_attributes']);
		}

		// 移除HTML中的注释
		if ($options['remove_comments']) {
			self::removeComments($node);
		}

		return $toString ? self::toHtmlString($node) : $node;
	}

	/**
	 * 移除HTML中的标签
	 * @param DOMNode $root
	 * @param array $removeTags
	 * @return DOMNode
	 */
	public static function removeTags(DOMNode $root, array $removeTags)
	{
		foreach ($removeTags as $tag) {
			// 使用XPath查询所有后代中的目标标签
			$elements = self::findAll($root, ".//{$tag}");
			while ($elements->length) {
				$element = $elements->item(0);
				$element->parentNode->removeChild($element);
			}
		}

		return $root;
	}

	/**
	 * 移除meta和link标签
	 *
	 * @return DOMNode
	 */
	public static function removeMeta(DOMNode $root)
	{
		return self::removeTags($root, ['meta', 'link']);
	}

	/**
	 * 移除样式（style标签和style属性）
	 *
	 * @return DOMNode
	 */
	public static function removeStyles(DOMNode $root)
	{
		return self::removeTags($root, ['style']);
	}

	/**
	 * 移除脚本（script标签和事件属性）
	 *
	 * @return DOMNode
	 */
	public static function removeScripts(DOMNode $root)
	{
		return self::removeTags($root, ['script']);
	}

	/**
	 * 遍历移除隐藏的元素
	 * @param DOMNode $root
	 * @return DOMNode
	 */
	public static function removeHiddenElements(DOMNode $root)
	{
		$xpath = self::xpath($root);

		// 查找所有可能隐藏的元素
		// 1. 通过 style 属性判断
		$hiddenNodes = $xpath->query(".//*[contains(@style, 'display:none') or contains(@style, 'display: none') or contains(@style, 'visibility:hidden') or contains(@style, 'visibility: hidden')]", $root);

		// 移除隐藏元素
		foreach ($hiddenNodes as $hiddenNode) {
			$hiddenNode->parentNode->removeChild($hiddenNode);
		}

		// 2. 通过 hidden 属性判断
		$hiddenAttrNodes = $xpath->query(".//*[@hidden]", $root);
		foreach ($hiddenAttrNodes as $hiddenNode) {
			$hiddenNode->parentNode->removeChild($hiddenNode);
		}

		// 3. 通过 aria-hidden 属性判断
		$ariaHiddenNodes = $xpath->query(".//*[@aria-hidden='true']", $root);
		foreach ($ariaHiddenNodes as $hiddenNode) {
			$hiddenNode->parentNode->removeChild($hiddenNode);
		}

		// 4. 通过 class 属性判断，class 中包含 hidden 或 hide，并是完整单词
		$classHiddenNodes = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' hidden ') or contains(concat(' ', normalize-space(@class), ' '), ' hide ')]");
		/** @var DOMElement $hiddenNode */
		foreach ($classHiddenNodes as $hiddenNode) {
			$hiddenNode->parentNode->removeChild($hiddenNode);
		}

		return $root;
	}

	/**
	 * 移除HTML注释
	 *
	 * @return DOMNode|null
	 */
	public static function removeComments(DOMNode $root)
	{
		$nodes = self::findAll($root, '//comment()');

		// 删除/修改 DOM 节点时，节点列表会实时更新，而 DOMNodeList 是一个活的（live）集合。
		while ($nodes->length) {
			$node = $nodes->item(0);
			$node->parentNode->removeChild($node);
		}

		return $root;
	}

	/**
	 * 递归清理节点属性
	 *
	 * @param DOMNode $root 要清理的节点
	 * @param array|null $allows 允许保留的属性列表
	 * @param array|null $excepts 强制移除的属性列表
	 * @return DOMNode 清理后的节点
	 */
	public static function removeAttributes(DOMNode $root, array $allows = null, array $excepts = null)
	{
		if (!$root instanceof DOMElement) {
			return $root;
		}

		// 遍历所有属性并判断是否保留
		if ($root->hasAttributes()) {
			for ($i = 0; $i < $root->attributes->length; $i++) {
				$attr = $root->attributes->item($i);
				$attrName = $attr->nodeName;

				// 如果指定了允许的属性列表
				if ($allows !== null && !Str::is($allows, $attrName)) {
					$root->removeAttribute($attrName);
				} // 如果指定了排除的属性列表
				elseif ($excepts !== null && Str::is($excepts, $attrName)) {
					$root->removeAttribute($attrName);
				}
			}
		}

		// 递归处理子节点
		foreach ($root->childNodes as $child) {
			self::removeAttributes($child, $allows, $excepts);
		}

		return $root;
	}

	/**
	 * 递归清理空节点
	 *
	 * @param DOMNode $root 要清理的节点
	 * @return DOMNode 清理后的节点
	 */
	public static function removeEmptyNodes(DOMNode $root)
	{
		// 1. 先递归处理子节点（从后往前避免索引偏移）
		while ($root->childNodes->length) {
			$child = $root->childNodes->item(0);

			if ($child instanceof DOMElement) {
				// 递归清理子节点
				self::removeEmptyNodes($child);

				// 检查是否为自闭合标签
				$isSelfClosing = in_array(strtolower($child->tagName), ['img', 'br', 'hr', 'input', 'meta', 'link']);

				// 如果不是自闭合标签且没有文本内容和子元素，则移除
				if (!$isSelfClosing && trim($child->textContent) === '' && $child->childNodes->length === 0) {
					$child->parentNode->removeChild($child);
				}
			} elseif ($child instanceof DOMText) {
				// 移除空白文本节点
				if (trim($child->nodeValue) === '') {
					$child->parentNode->removeChild($child);
				}
			}
		}

		return $root;
	}


	/**
	 * 获取节点属性
	 * @param DOMElement $element
	 * @return array
	 */
	public static function attributes(DOMElement $element)
	{
		$attributes = [];

		foreach ($element->attributes as $attr) {
			$attributes[$attr->nodeName] = $attr->nodeValue;
		}

		return $attributes;
	}

	/**
	 * 获取节点深度
	 * @param DOMNode $current
	 * @param bool $excludeHtmlBody
	 * @return int
	 */
	public static function depth(DOMNode $current, bool $excludeHtmlBody = true)
	{
		$depth = 0;

		while (($current = $current->parentNode) && !($current instanceof DOMDocument)) {
			if ($excludeHtmlBody && in_array($current->tagName, ['html', 'body'])) {
				break;
			}

			$depth++;
		}

		return $depth;
	}

	/**
	 * 获取节点的父级元素
	 * @param DOMNode $current
	 * @param bool $excludeHtmlBody
	 * @return array
	 */
	public static function parents(DOMNode $current, bool $excludeHtmlBody = true)
	{
		$parents = [];

		/** @var DOMElement $current */
		while (($current = $current->parentNode) && !($current instanceof DOMDocument)) {
			if ($excludeHtmlBody && in_array($current->tagName, ['html', 'body'])) {
				break;
			}

			$parents[] = $current;
		}

		return $parents;
	}

	/**
	 * 获取节点的父级元素标签
	 * @param DOMElement $current
	 * @return array
	 */
	public static function parentTags(DOMElement $current)
	{
		$parents = self::parents($current);
		$parents = array_reverse($parents);
		return array_map(function ($node) {
			// 获取当前节点在同兄弟位置
			$position = self::siblingPosition($node);
			return $node->tagName . "[{$position}]";
		}, $parents);
	}

	/**
	 * 获取节点标签
	 * @param DOMElement $current
	 * @param bool $serialize
	 * @return array|string
	 */
	public static function tags(DOMElement $current, $serialize = false)
	{
		$tags = self::parentTags($current);

		// 获取当前节点在同兄弟位置
		$position = self::siblingPosition($current);
		$tags[] = $current->tagName . "[$position]";

		return $serialize ? implode('/', $tags) : $tags;
	}

	/**
	 * 获取节点最接近的元素
	 * @param DOMElement $current
	 * @param string $selector
	 * @return DOMNode|null
	 */
	public static function closest(DOMElement $current, $selector)
	{
		$selector = self::selectorToXpathExpression($selector);
		while ($current && !($current instanceof DOMDocument)) {
			if (self::match($current, $selector)) {
				return $current;
			}

			$current = $current->parentNode;
		}

		return null;
	}

	/**
	 * 获取兄弟节点位置
	 * @param DOMElement $current
	 * @return int
	 */
	public static function siblingPosition(DOMElement $current)
	{
		$index = 0;

		$siblings = $current->parentNode->childNodes;
		foreach ($siblings as $sibling) {
			if ($sibling === $current) {
				break;
			} elseif ($sibling instanceof DOMElement && $sibling->tagName === $current->tagName) {
				$index++;
			}
		}

		return $index + 1;
	}

	/**
	 * 判断 $child 是否包含在 $ancestor 中
	 * @param DOMNode $ancestor
	 * @param DOMNode $child
	 * @return bool
	 */
	public static function contains(DOMNode $ancestor, DOMNode $child)
	{
		if ($ancestor === $child || $ancestor->isSameNode($child)) {
			return false;
		}

		for ($n = $child; $n !== null; $n = $n->parentNode) {
			if ($n === $ancestor) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 判断节点是否匹配指定的XPath表达式
	 *
	 * @param string $selector XPath表达式
	 * @return bool
	 */
	public static function match(DOMNode $node, $xpathExpression)
	{
		if (!$node instanceof DOMElement) {
			return false;
		}

		// 加 . 限制上下文为当前节点本身
		return self::xpath($node)->evaluate('boolean(.' . $xpathExpression . ')', $node);
	}

	/**
	 * 提节点文本，根据 xpath rules 依次匹配，并返回第一个匹配到的结果
	 * @param DOMNode $root
	 * @param array|string $rules
	 * @return string|null
	 */
	public static function readValue(DOMNode $root, $xpathRules)
	{
		$node = self::find($root, $xpathRules);
		if (!$node) {
			return null;
		}

		if ($node instanceof DOMElement) {
			$value = $node->textContent;
		} else {
			$value = $node->nodeValue;
		}

		return trim($value);
	}

	/**
	 * 提节点文本，根据 xpath rules 获取所有匹配到的结果
	 * @param DOMNode $root
	 * @param array|string $xpathRules
	 * @param callable|null $transform
	 * @return array
	 */
	public static function readValues(DOMNode $root, $xpathRules, callable $transform = null)
	{
		$nodes = self::findAll($root, $xpathRules);
		if (!$nodes) {
			return [];
		}

		return array_map($transform ?: [self::class, 'value'], iterator_to_array($nodes));
	}

	/**
	 * 获取节点值
	 * @param mixed $node
	 * @return mixed
	 */
	public static function value($node)
	{
		$value = $node;

		if ($node instanceof DOMNode) {
			if ($node instanceof DOMElement) {
				$value = $node->textContent;
			} else {
				$value = $node->nodeValue;
			}
		}

		return $value;
	}

	/**
	 * 通过XPath查找第一个匹配的节点
	 *
	 * @param string $xpath XPath表达式
	 * @return DOMNode|null
	 */
	public static function find(DOMNode $root, string $xpath)
	{
		$nodes = self::findAll($root, $xpath);

		return $nodes->length > 0 ? $nodes->item(0) : null;
	}

	/**
	 * 通过XPath查找所有匹配的节点
	 *
	 * @param string $xpath XPath表达式
	 * @return DOMNodeList
	 */
	public static function findAll(DOMNode $root, string $xpath)
	{
		return self::xpath($root)->query($xpath, $root);
	}

	/**
	 * 通过CSS选择器查找第一个匹配的节点
	 *
	 * @param string $selector CSS选择器
	 * @return DOMNode|null
	 */
	public static function querySelector(DOMNode $root, $selector)
	{
		$xpathExpression = static::selectorToXpathExpression($selector);

		return self::find($root, $xpathExpression);
	}

	/**
	 * 通过CSS选择器查找所有匹配的节点
	 *
	 * @param string $selector CSS选择器
	 * @return DOMNodeList
	 */
	public static function querySelectorAll(DOMNode $root, $selector)
	{
		$xpathExpression = static::selectorToXpathExpression($selector);

		return self::findAll($root, $xpathExpression);
	}

	/**
	 * CSS选择器转XPath选择器
	 *
	 * @param string $selector CSS选择器
	 * @return string XPath选择器
	 */
	public static function selectorToXpathExpression($selector)
	{
		// 处理空选择器
		if (empty($selector)) {
			return '.';
		}

		// 处理特殊选择器
		if ($selector === '*') {
			return '//*';
		}

		// 分割选择器组（逗号分隔）
		$selectors = explode(',', $selector);
		$xpaths = [];

		foreach ($selectors as $sel) {
			$sel = trim($sel);
			if (empty($sel)) {
				continue;
			}

			// 处理后代选择器（空格分隔）
			$parts = preg_split('/\s+/', $sel);
			$xpath = '';

			foreach ($parts as $part) {
				if (empty($part)) {
					continue;
				}

				// 处理不同的选择器类型
				if (strpos($part, '#') === 0) {
					// ID选择器: #id
					$id = substr($part, 1);
					$xpath .= "//*[@id='{$id}']";
				} elseif (strpos($part, '.') === 0) {
					// 类选择器: .class
					$class = substr($part, 1);
					$xpath .= "//*[contains(concat(' ', @class, ' '), ' {$class} ')]";
				} elseif (preg_match('/^([\w-]+)?\[([\w-]+)(?:([~|^$*]?=)"?([^"\]]*)"?)]?$/', $part, $matches)) {
					// 属性选择器: tag[attr="value"] 或 [attr="value"]
					$tag = isset($matches[1]) && $matches[1] ? $matches[1] : '*';
					$attr = $matches[2];
					$operator = isset($matches[3]) ? $matches[3] : '';
					$value = isset($matches[4]) ? $matches[4] : '';

					switch ($operator) {
						case '':
							$xpath .= "//{$tag}[@{$attr}]";
							break;
						case '=':
							$xpath .= "//{$tag}[@{$attr}='{$value}']";
							break;
						case '~=':
							$xpath .= "//{$tag}[contains(concat(' ', @{$attr}, ' '), ' {$value} ')]";
							break;
						case '|=':
							$xpath .= "//{$tag}[@{$attr}='{$value}' or starts-with(@{$attr}, '{$value}-')]";
							break;
						case '^=':
							$xpath .= "//{$tag}[starts-with(@{$attr}, '{$value}')]";
							break;
						case '$=':
							$xpath .= "//{$tag}[substring(@{$attr}, string-length(@{$attr}) - " . (strlen($value) - 1) . ")='{$value}']";
							break;
						case '*=':
							$xpath .= "//{$tag}[contains(@{$attr}, '{$value}')]";
							break;
						default:
							$xpath .= "//{$tag}[@{$attr}]";
					}
				} else {
					// 标签选择器: tag
					$tag = $part;
					$xpath .= "//{$tag}";
				}
			}

			$xpaths[] = $xpath;
		}

		return implode(' | ', $xpaths);
	}


	/**
	 * 遍历节点树
	 *
	 * @param callable $callback 回调函数
	 * @return DOMNode
	 */
	public function each(DOMNode $root, callable $callback)
	{
		$callback($root);

		// 递归处理所有子节点
		for ($i = $root->childNodes->length - 1; $i >= 0; $i--) {
			$childNode = $root->childNodes->item($i);
			static::each($childNode, $callback);
		}

		return $root;
	}

	/**
	 * 补全相对链接
	 *
	 * @param DOMNode $root 要处理的节点
	 * @param string $baseUrl 基础URL
	 * @return DOMNode 处理后的节点
	 */
	public static function completeRelativeLinks(DOMNode $root, string $baseUrl)
	{
		// 如果是元素节点，检查是否有需要补全的链接属性
		if ($root instanceof DOMElement) {
			// 处理 href 属性
			if ($root->hasAttribute('href')) {
				$href = $root->getAttribute('href');
				if (!preg_match('/^(https?:)?\/\//i', $href)) {
					$root->setAttribute('href', rtrim($baseUrl, '/') . '/' . ltrim($href, '/'));
				}
			}

			// 处理 src 属性
			if ($root->hasAttribute('src')) {
				$src = $root->getAttribute('src');
				if (!preg_match('/^(https?:)?\/\//i', $src)) {
					$root->setAttribute('src', rtrim($baseUrl, '/') . '/' . ltrim($src, '/'));
				}
			}
		}

		// 递归处理所有子节点
		foreach ($root->childNodes as $childNode) {
			static::completeRelativeLinks($childNode, $baseUrl);
		}

		return $root;
	}

	/**
	 * 获取节点内容
	 * @param DOMNode|null $root
	 * @return string
	 */
	public static function toHtmlString(DOMNode $root = null, bool $compressWhitespace = true)
	{
		if ($root === null) {
			return '';
		}

		// 获取节点内容
		$html = $root->ownerDocument->saveHTML($root);

		// 把 saveHTML 产生的 HTML 实体还原成真实 Unicode
		$html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

		// 压缩空白
		return $compressWhitespace ? static::compressWhitespace($html) : $html;
	}

	/**
	 * 空格缩进替换成指定宽度
	 * @param string $html
	 * @param int $indentSize
	 * @return string
	 */
	public static function reindent(string $html, int $indentSize)
	{
		if ($indentSize < 1) {
			return preg_replace('/>\s+</', '><', $html);
		}

		$spaces = str_repeat(' ', $indentSize);
		return preg_replace_callback('/^(\s+)/m', function ($m) use ($spaces) {
			$level = (int)(strlen($m[1]) / 2);
			return str_repeat($spaces, $level);
		}, $html);
	}

	/**
	 * 压缩HTML空白字符（优化页面加载性能）
	 *
	 * @param string $html 原始HTML内容
	 * @return string 压缩后的HTML
	 */
	public static function compressWhitespace(string $html)
	{
		// 1. 压缩多个空白符为单个空格
		$html = preg_replace('/\s+/u', ' ', $html);

		// 2. 移除标签前后的空格
		$html = preg_replace('/>\s+</u', '><', $html);

		// 3. 移除开头和结尾的空白符
		return trim($html);
	}
}
