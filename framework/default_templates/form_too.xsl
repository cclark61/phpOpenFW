<?xml version="1.0" encoding="UTF-8"?>

<!DOCTYPE xsl:stylesheet [ 
   <!ENTITY nbsp "&#160;" >
   <!ENTITY bull "&#149;" >
   <!ENTITY copy "&#169;" >
   <!ENTITY amp "&#38;" >
]>
   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" omit-xml-declaration="yes" version="1.0" encoding="UTF-8" indent="yes" />

<!--********************************************************************-->
<!--********************************************************************-->
<!-- Form Template -->
<!--********************************************************************-->
<!--********************************************************************-->
<xsl:template match="form">

    <form>

        <xsl:for-each select="@*">
            <xsl:attribute name="{name()}">
                <xsl:value-of select="." disable-output-escaping="yes" />
            </xsl:attribute>
        </xsl:for-each>

		<!--********************************************************************-->
		<!-- Hidden Elements -->
		<!--********************************************************************-->
		<xsl:for-each select="./hidden_elements/*">
			<xsl:value-of select="concat('&#xA;', .)" disable-output-escaping="yes" />
		</xsl:for-each>

		<!--********************************************************************-->
		<!-- Display Elements -->
		<!--********************************************************************-->
		<xsl:choose>
			<xsl:when test="./form_label">
				<xsl:text>&#xA;</xsl:text>
		        <fieldset>
		        	<xsl:text>&#xA;</xsl:text>
					<legend><xsl:value-of select="./form_label" disable-output-escaping="yes" /></legend>
					<xsl:call-template name="process_form_elements">
						<xsl:with-param name="base" select="." />
					</xsl:call-template>
				<xsl:text>&#xA;</xsl:text>
		        </fieldset>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="process_form_elements">
					<xsl:with-param name="base" select="." />
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>

		<!--********************************************************************-->
		<!-- New Line at end of form -->
		<!--********************************************************************-->
		<xsl:text>&#xA;</xsl:text>

    </form>

</xsl:template>

<!--********************************************************************-->
<!--********************************************************************-->
<!-- Process Form Elements Template -->
<!--********************************************************************-->
<!--********************************************************************-->
<xsl:template name="process_form_elements">
	<xsl:param name="base" />

	<xsl:for-each select="$base/form_elements/*">

		<xsl:choose>

			<!--********************************************************************-->
			<!-- Start Section -->
			<!--********************************************************************-->
			<xsl:when test="name()=string('start_section')">
				<xsl:value-of select="concat('&#xA;&lt;', @tag)" disable-output-escaping="yes" />

				<xsl:if test="count(@*) > 1">
					<xsl:value-of select="' '" disable-output-escaping="yes" />
				</xsl:if>

				<xsl:for-each select="@*">
					<xsl:if test="name() != 'tag'">
		                <xsl:value-of select="concat(name(), '=&quot;', ., '&quot;')" disable-output-escaping="yes" />
					</xsl:if>
		        </xsl:for-each>

				<xsl:value-of select="string('&gt;')" disable-output-escaping="yes" />

				<!-- Content -->
				<xsl:if test=". != ''">
					<xsl:value-of select="." disable-output-escaping="yes" />
				</xsl:if>
			</xsl:when>

			<!--********************************************************************-->
			<!-- End Section -->
			<!--********************************************************************-->
			<xsl:when test="name()=string('end_section')">
				<xsl:value-of select="concat('&#xA;&lt;/', @tag, '&gt;')" disable-output-escaping="yes" />
			</xsl:when>

			<!--********************************************************************-->
			<!-- Data Element -->
			<!--********************************************************************-->
			<xsl:otherwise>
				<xsl:value-of select="concat('&#xA;', .)" disable-output-escaping="yes" />
			</xsl:otherwise>

		</xsl:choose>

	</xsl:for-each>

</xsl:template>

</xsl:stylesheet>