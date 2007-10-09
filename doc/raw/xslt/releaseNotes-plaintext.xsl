<?xml version="1.0" encoding="ISO-8859-1"?>
<!-- 
 @package concerto.docs
 
 @copyright Copyright &copy; 2005, Middlebury College
 @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 
 @version $Id: releaseNotes-plaintext.xsl,v 1.1 2007/10/09 18:47:56 adamfranco Exp $
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="trim.xsl"/>
<xsl:import href="paragraphs.xsl"/>
<xsl:output format="text" />
<xsl:strip-space elements="fix change new important" />
<!--
///////////////////////////////////////////////////////////////////////
// changelog
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="changelog">Name: <xsl:value-of select="@name" /> Release Notes
(See the <xsl:value-of select="@name" /> change log for more details)

<xsl:for-each select="version">
v. <xsl:value-of select="@number" /><xsl:if test="@date!=''"> (<xsl:value-of select="@date" />)</xsl:if>
----------------------------------------------------
<xsl:call-template name="addNewlines">
	<xsl:with-param name="maxCharacters" select="84"/>
	<xsl:with-param name="remainingString">
		<xsl:call-template name="singleLineParagraphs">
			<xsl:with-param name="s" select="releaseNotes"/>
		</xsl:call-template>
	</xsl:with-param>
</xsl:call-template>
<xsl:text>

</xsl:text>
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>
