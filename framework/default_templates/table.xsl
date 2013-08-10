<?xml version="1.0" encoding="ISO-8859-1"?>

<!DOCTYPE xsl:stylesheet [ 
   <!ENTITY nbsp "&#160;" >
   <!ENTITY bull "&#149;" >
   <!ENTITY copy "&#169;" >
   <!ENTITY amp "&#38;" >
]>
   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method='xml' omit-xml-declaration="yes" version='1.0' encoding='UTF-8' indent='yes' />

<xsl:template match="table">

    <xsl:variable name="columns">
        <xsl:value-of select="./table_data/columns"/>
    </xsl:variable>

    <!-- Start Fieldset (if necessary) -->
    <xsl:if test="./table_data/fieldset">
        <xsl:value-of select="string('&lt;fieldset')" disable-output-escaping="yes" />
        
        <!-- Fieldset ID -->
        <xsl:if test="./table_data/fieldset/id">
        	<xsl:value-of select="concat(' id=&quot;', ./table_data/fieldset/id, '&quot;')" disable-output-escaping="yes" />
        </xsl:if>

        <!-- Fieldset Class -->
        <xsl:if test="./table_data/fieldset/class">
        	<xsl:value-of select="concat(' class=&quot;', ./table_data/fieldset/class, '&quot;')" disable-output-escaping="yes" />
        </xsl:if>

        <xsl:value-of select="string('&gt;')" disable-output-escaping="yes" />
        
        <!-- Legend -->
        <xsl:if test="./table_data/fieldset/legend">
        	<legend><xsl:value-of select="./table_data/fieldset/legend" disable-output-escaping="yes" /></legend>
        </xsl:if>
    </xsl:if>

    <table cellspacing="0">

    	<!-- Attributes -->
        <xsl:for-each select="@*">
            <xsl:attribute name="{name()}">
                <xsl:value-of select="."/>
            </xsl:attribute>
        </xsl:for-each>
        
        <!-- Label -->
        <xsl:if test="./table_data/caption">
		      <caption><xsl:value-of select="./table_data/caption" disable-output-escaping="yes" /></caption>
        </xsl:if>
        
        <!-- Header Elements -->
        <xsl:if test="./table_data/elements/header/*">
        	<thead>
        	<xsl:for-each select="./table_data/elements/header/*">
        		<tr>
            		<xsl:for-each select="./*">
            			<xsl:call-template name="print_cell">
            				<xsl:with-param name="curr_loc" select="." />
            			</xsl:call-template>
               		</xsl:for-each>
            	</tr>
        	</xsl:for-each>
        	</thead>
        </xsl:if>
        
        <!-- Body Elements -->
        <xsl:if test="./table_data/elements/body/*">
        	<tbody>
        	<xsl:for-each select="./table_data/elements/body/*">
        		<tr>
        			<xsl:if test="//table/table_data/alt_rows = 1 and position() mod 2 = 0">
					    <xsl:attribute name="class">alt</xsl:attribute>
					</xsl:if>
            		<xsl:for-each select="./*">
            			<xsl:call-template name="print_cell">
            				<xsl:with-param name="curr_loc" select="." />
            			</xsl:call-template>
               		</xsl:for-each>
            	</tr>
        	</xsl:for-each>
        	</tbody>
        </xsl:if>
        
        <!-- Footer Elements -->
        <xsl:if test="./table_data/elements/footer/*">
        	<tfooter>
        	<xsl:for-each select="./table_data/elements/footer/*">
        		<tr>
            		<xsl:for-each select="./*">
            			<xsl:call-template name="print_cell">
            				<xsl:with-param name="curr_loc" select="." />
            			</xsl:call-template>
               		</xsl:for-each>
            	</tr>
        	</xsl:for-each>
        	</tfooter>
        </xsl:if>

    </table>

        <!-- End Fieldset (if necessary) -->
        <xsl:if test="./table_data/fieldset">
        	<xsl:value-of select="string('&lt;/fieldset&gt;')" disable-output-escaping="yes" />
        </xsl:if>

</xsl:template>

<!--************************************************************-->
<!-- Print Cell Template -->
<!--************************************************************-->
<xsl:template name="print_cell">
	<xsl:param name="curr_loc" />
	
	<xsl:choose>
		<xsl:when test="$curr_loc/type = 'header_cell'">
			<th>
				<xsl:attribute name="colspan"><xsl:value-of select="$curr_loc/cols"/></xsl:attribute>
				<xsl:for-each select="./attrs/*">
					<xsl:attribute name="{name()}">
						<xsl:value-of select="." />
					</xsl:attribute>
				</xsl:for-each>
				<xsl:choose>
                	<xsl:when test="content != ''">
						<xsl:value-of select="$curr_loc/content" disable-output-escaping="yes" />
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="string('&amp;nbsp;')" disable-output-escaping="yes" />
					</xsl:otherwise>
				</xsl:choose>
			</th>
		</xsl:when>
		<xsl:otherwise>
			<td>
				<xsl:attribute name="colspan"><xsl:value-of select="$curr_loc/cols"/></xsl:attribute>
				<xsl:for-each select="./attrs/*">
					<xsl:attribute name="{name()}">
						<xsl:value-of select="." />
					</xsl:attribute>
				</xsl:for-each>
				<xsl:choose>
					<xsl:when test="content != ''">
						<xsl:value-of select="$curr_loc/content" disable-output-escaping="yes" />
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="string('&amp;nbsp;')" disable-output-escaping="yes" />
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

</xsl:stylesheet>