<?xml version="1.0" encoding="ISO-8859-1"?>
<!-- 
 @package concerto.docs
 
 @copyright Copyright &copy; 2005, Middlebury College
 @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 
 @version $Id: changelog-simplehtml.xsl,v 1.1 2007/05/04 20:47:24 adamfranco Exp $
 -->
 
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<!--
///////////////////////////////////////////////////////////////////////
// changelog
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="changelog">

<html>
	<head>
		<style type="text/css">
			body {
				font-family: Verdana; font-size: 12px;
			}
			
			h1, h2 {
				color: #005;
			}
			
			h1 {
				font-size: 18pt;
			}
			
			li {
				padding-bottom: 3px;
			}
		</style>
		<title><xsl:value-of select="@name" /></title>

	</head>
	<body>
		<h1><xsl:value-of select="@name" /></h1>
	
	
<xsl:for-each select="version">
		<h2>Version <xsl:value-of select="@number" /></h2>
		<xsl:if test="@date!=''"><h3><xsl:value-of select="@date" /></h3></xsl:if>

		<ul>
			<xsl:apply-templates />
		</ul>
		<br />
</xsl:for-each>

	</body>
</html>
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// fix
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="fix">
	<li> Bug Fix: <xsl:call-template name="entry" /></li>	
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// change
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="change">
	<li> Change: <xsl:call-template name="entry" /></li>
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// new
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="new">
	<li> New feature: <xsl:call-template name="entry" /></li>
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// important
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="important">
	<li> <span style='color: red'>*** IMPORTANT ***</span> Change: <xsl:call-template name="entry" /></li>
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// entry
///////////////////////////////////////////////////////////////////////
-->
<xsl:template name="entry">
	<xsl:if test="@ref">
		<xsl:choose>
			<xsl:when test="@reftype">
				<xsl:variable name="reftype" select="@reftype" />
				<xsl:variable name="trackerid" select="//reftypes/reftype[@name = $reftype]" />
        		<a>
        			<xsl:attribute name="href">
        				http://sourceforge.net/tracker/index.php?func=detail&amp;aid=<xsl:value-of select="@ref" />&amp;group_id=<xsl:value-of select="//groupid" />&amp;atid=<xsl:value-of select="$trackerid" />
        			</xsl:attribute>
        			#<xsl:value-of select="@ref" />
        		</a>
			</xsl:when>
			<xsl:otherwise>
				#<xsl:value-of select="@ref" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:if>
	<xsl:text> </xsl:text><xsl:value-of select="." />
	<xsl:if test="@author">
		<xsl:text> (</xsl:text>
		<em>
			<xsl:call-template name="authors">
				<xsl:with-param name="str" select="@author"/>
			</xsl:call-template>
		</em>
		<xsl:text>)</xsl:text>
	</xsl:if>
</xsl:template>

<xsl:template name="authors">
  <xsl:param name="str"/>
  <xsl:choose>
    <xsl:when test="contains($str,',')">
    	<xsl:value-of select="//authors/name[@short=substring-before($str,',')]" />
  	
      <xsl:text>, </xsl:text>
      <xsl:call-template name="authors">
        <xsl:with-param name="str" select="substring-after($str,',')"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
    	<xsl:value-of select="//authors/name[@short=$str]" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>
