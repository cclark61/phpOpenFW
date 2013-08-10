<?xml version="1.0" encoding="ISO-8859-1"?>

<!DOCTYPE xsl:stylesheet [ 
   <!ENTITY nbsp "&#160;" >
   <!ENTITY bull "&#149;" >
   <!ENTITY copy "&#169;" >
   <!ENTITY amp "&#38;" >
]>
   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method='xml' omit-xml-declaration="yes" version='1.0' encoding='UTF-8' indent='yes' />

<!--********************************************************************-->
<!--********************************************************************-->
<!-- Table Group Template -->
<!--********************************************************************-->
<!--********************************************************************-->
<xsl:template match="table_group">
	<xsl:for-each select="//table_group/table">
    	<xsl:call-template name="rs_list" />
    </xsl:for-each>
</xsl:template>

<!--********************************************************************-->
<!--********************************************************************-->
<!-- Table Template -->
<!--********************************************************************-->
<!--********************************************************************-->
<xsl:template match="table">
	<xsl:call-template name="rs_list" />
</xsl:template>

<!--********************************************************************-->
<!--********************************************************************-->
<!-- RS_LIST Template -->
<!--********************************************************************-->
<!--********************************************************************-->
<xsl:template name="rs_list">
    <xsl:if test="./label">
        <xsl:value-of select="string('&lt;fieldset&gt;')" disable-output-escaping="yes" />
        <legend>
        	<xsl:choose>
        		<xsl:when test="./label/*">
					<xsl:copy-of select="label/*"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="./label/." disable-output-escaping="yes" />
				</xsl:otherwise>
			</xsl:choose>
        </legend>
    </xsl:if>
    <table cellspacing="0">
        <xsl:for-each select="@*">
            <xsl:attribute name="{name()}">
                <xsl:value-of select="."/>
            </xsl:attribute>
        </xsl:for-each>
        <xsl:if test="./header">
			<thead>
            <xsl:for-each select="./header/row">
                <tr>
                <xsl:for-each select="cell">
                    <th>
                    	<xsl:for-each select="@*">
                            <xsl:attribute name="{name()}">
                                <xsl:value-of select="."/>
                            </xsl:attribute>
                        </xsl:for-each>
                        <xsl:value-of select="." disable-output-escaping="yes" />
                    </th>
                </xsl:for-each>
                </tr>
            </xsl:for-each>
		</thead>
        </xsl:if>
        <xsl:if test="./content">
			<tbody>
            <xsl:for-each select="./content/row">
                <tr>
                    <xsl:for-each select="@*">
                        <xsl:attribute name="{name()}">
                            <xsl:value-of select="."/>
                        </xsl:attribute>
                    </xsl:for-each>
                <xsl:for-each select="./cell">
                    <td>
                        <xsl:for-each select="@*">
                            <xsl:attribute name="{name()}">
                                <xsl:value-of select="."/>
                            </xsl:attribute>
                        </xsl:for-each>
                        <xsl:choose>
                            <xsl:when test="./*">
                                <xsl:copy-of select="./*"/>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:value-of select="."  disable-output-escaping="yes" />
                            </xsl:otherwise>
                        </xsl:choose>
                    </td>
                </xsl:for-each>
                </tr>
            </xsl:for-each>
		</tbody>
        </xsl:if>
    </table>
    <xsl:if test="./label">
        <xsl:value-of select="string('&lt;/fieldset&gt;')" disable-output-escaping="yes" />
    </xsl:if>
</xsl:template>

</xsl:stylesheet>