<?xml version="1.0" encoding="ISO-8859-1"?>
<!-- 
 @package concerto.docs
 
 @copyright Copyright &copy; 2005, Middlebury College
 @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 
 @version $Id: changelog-plaintext.xsl,v 1.2 2007/05/07 15:22:27 adamfranco Exp $
 -->
 
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:import href="trim.xsl"/>

<xsl:output format="text" />
<xsl:strip-space elements="fix change new important" />

<!--
///////////////////////////////////////////////////////////////////////
// changelog
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="changelog">
Name: <xsl:value-of select="@name" />
<xsl:text>

</xsl:text>
	<xsl:for-each select="version">
v. <xsl:value-of select="@number" /><xsl:if test="@date!=''"> (<xsl:value-of select="@date" />)</xsl:if>
----------------------------------------------------<xsl:apply-templates />

	</xsl:for-each>
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// fix
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="fix">
	* Bug Fix: <xsl:call-template name="entry" />
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// change
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="change">
	* Change: <xsl:call-template name="entry" />
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// new
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="new">
	* New feature: <xsl:call-template name="entry" />
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// important
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="important">
	**** IMPORTANT *** Change: <xsl:call-template name="entry" />
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// entry
///////////////////////////////////////////////////////////////////////
-->
<xsl:template name="entry">
	<xsl:if test="@ref">#<xsl:value-of select="@ref" /><xsl:text> </xsl:text></xsl:if>
	<xsl:text>&#x0A;&#x09;&#x09;</xsl:text>
	<xsl:call-template name="addNewlines">
		<xsl:with-param name="maxCharacters" select="76"/>
		<xsl:with-param name="remainingString">
			<xsl:value-of select="normalize-space(translate(translate(.,'&#10;',''), '&#x0A;', ' '))" />
		</xsl:with-param>
	</xsl:call-template>
	
	<xsl:if test="@author">
		<xsl:text>&#x0A;&#x09;&#x09;</xsl:text>
		<xsl:text>(</xsl:text>
		
		<xsl:call-template name="authors">
			<xsl:with-param name="str" select="@author"/>
		</xsl:call-template>

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
