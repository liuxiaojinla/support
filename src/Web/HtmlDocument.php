<?php

namespace Xin\Support\Web;

use DOMDocument;
use DOMNode;
use DOMXPath;

class HtmlDocument
{
	/**
	 * @var DOMDocument DOM文档对象
	 */
	protected $document;

	/**
	 * @var DOMXPath XPath查询对象
	 */
	protected $xpath;

	/**
	 * @var DOMNode 主要操作节点（默认是文档根节点）
	 */
	protected $mainNode;

	public function __construct($html)
	{
		$this->document = Html::document($html);
		$this->xpath = Html::xpath($this->document);
	}

	/**
	 * 获取主要操作节点（延迟初始化）
	 *
	 * @return DOMNode
	 */
	public function getMainNode()
	{
		if (!$this->mainNode) {
			$this->mainNode = $this->document->documentElement;
		}

		return $this->mainNode;
	}

	/**
	 * 尝试设置主要操作节点
	 *
	 * @param array $xpaths XPath列表
	 * @return DOMNode|null
	 */
	public function findTrySetMainNode(array $xpaths)
	{
		foreach ($xpaths as $xpath) {
			$element = $this->find($xpath);
			if ($element) {
				$this->setMainNode($element);

				return $element;
			}
		}

		return null;
	}

	/**
	 * 尝试设置主要操作节点
	 *
	 * @param array $selectors CSS选择器列表
	 * @return DOMNode|null
	 */
	public function querySelectorTrySetMainNode(array $selectors)
	{
		$xpaths = array_map(function ($selector) {
			return static::selectorToXpath($selector);
		}, $selectors);

		return $this->findTrySetMainNode($xpaths);
	}

	/**
	 * 设置主要操作节点
	 * @param DOMNode $mainNode
	 * @return $this
	 */
	public function setMainNode(DOMNode $mainNode)
	{
		$this->mainNode = $mainNode;

		return $this;
	}

	/**
	 * 移除HTML中的标签
	 * @param array $removeTags
	 * @return $this
	 */
	public function removeTags(array $removeTags)
	{
		Html::removeTags($this->document, $removeTags);

		return $this;
	}

	/**
	 * 移除空节点（无有效内容和子节点的元素）
	 *
	 * @return $this
	 */
	public function removeEmptyNodes()
	{
		Html::removeEmptyNodes($this->getMainNode());

		return $this;
	}

	/**
	 * 移除HTML注释
	 *
	 * @return $this
	 */
	public function removeComments()
	{
		Html::removeComments($this->getMainNode());

		return $this;
	}

	/**
	 * 获取DOMDocument对象
	 *
	 * @return DOMDocument
	 */
	public function getDocument()
	{
		return $this->document;
	}

	/**
	 * 获取DOMXPath对象
	 *
	 * @return DOMXPath
	 */
	public function getXPath()
	{
		return $this->xpath;
	}


	/**
	 * 将节点转换为HTML字符串
	 *
	 * @param DOMNode|null $node 要转换的节点（默认使用主要操作节点）
	 * @param bool $compressWhitespace 是否压缩空白字符（默认true）
	 * @return string
	 */
	public function toString(DOMNode $node = null, bool $compressWhitespace = true)
	{
		return Html::toHtmlString($node ?: $this->getMainNode(), $compressWhitespace);
	}

	/**
	 * 魔术方法：转换为字符串时返回HTML内容
	 *
	 * @return string
	 */
	public function __toString()
	{
		try {
			return $this->toString();
		} catch (\Exception $e) {
			return '';
		}
	}
}
