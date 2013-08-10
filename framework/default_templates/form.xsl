<?xml version="1.0" encoding="ISO-8859-1"?>

<!DOCTYPE xsl:stylesheet [ 
   <!ENTITY nbsp "&#160;" >
   <!ENTITY bull "&#149;" >
   <!ENTITY copy "&#169;" >
   <!ENTITY amp "&#38;" >
]>
   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method='xml' omit-xml-declaration="yes" version='1.0' encoding='UTF-8' indent='yes' />

<xsl:template match="form">
    <xsl:variable name="columns">
        <xsl:value-of select="data/columns"/>
    </xsl:variable>
    <xsl:if test="script">
        <xsl:value-of select="script" disable-output-escaping="yes" />
    </xsl:if>
    <form>
        <xsl:for-each select="@*">
            <xsl:attribute name="{name()}">
                <xsl:value-of select="."/>
            </xsl:attribute>
        </xsl:for-each>
        
        <fieldset>
			<xsl:if test="data/form_label">
				  <legend><xsl:value-of select="data/form_label" disable-output-escaping="yes" /></legend>
			</xsl:if>
			
			<!-- Hidden Elements -->
			<xsl:for-each select="data/hidden_elements/*">
				<xsl:value-of select="." disable-output-escaping="yes" />
			</xsl:for-each>
			
			<table cellspacing="0">
				<!-- Table Headers -->
				<xsl:if test="data/headers/*">
					<tr>
						<xsl:for-each select="data/headers/*">
							<th><xsl:value-of select="." disable-output-escaping="yes" /></th>
						</xsl:for-each>
					</tr>
				</xsl:if>
				
				<!-- Form Elements -->
				<xsl:for-each select="data/elements/*">
					<xsl:if test="name()=string('row')">
						<tr>
							<xsl:for-each select="@*">
								<xsl:attribute name="{name()}">
									<xsl:value-of select="."/>
								</xsl:attribute>
							</xsl:for-each>
							<xsl:for-each select="./form_element">
								<td>
									<xsl:for-each select="@*">
										<xsl:attribute name="{name()}">
											<xsl:value-of select="."/>
										</xsl:attribute>
									</xsl:for-each>
									<xsl:value-of select="." disable-output-escaping="yes" />
								</td>
							</xsl:for-each>
						</tr>
					</xsl:if>
					
					<!-- Fieldset -->
					<xsl:if test="name()=string('fieldset')">
						<xsl:choose>
							<xsl:when test="@marker=string('start')">
								<xsl:value-of select="string('&lt;tr&gt;')" disable-output-escaping="yes" />
								<xsl:value-of select="concat('&lt;td colspan=&quot;', $columns, '&quot;&gt;')" disable-output-escaping="yes" />
								<xsl:value-of select="string('&lt;fieldset')" disable-output-escaping="yes" />
								<!-- Fieldset ID -->
								<xsl:if test="@id">
									<xsl:value-of select="concat(' id=&quot;', @id, '&quot;')" disable-output-escaping="yes" />
								</xsl:if>
								
								<!-- Fieldset Class -->
								<xsl:if test="@class">
									<xsl:value-of select="concat(' class=&quot;', @class, '&quot;')" disable-output-escaping="yes" />
								</xsl:if>
		
								<xsl:value-of select="string('&gt;')" disable-output-escaping="yes" />
								
								<!-- Legend -->
								<legend><xsl:value-of select="./legend" disable-output-escaping="yes" /></legend>
								
								<!-- Begin Table -->
								<xsl:value-of select="string('&lt;table cellspacing=&quot;0&quot;&gt;')" disable-output-escaping="yes" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="string('&lt;/table&gt;')" disable-output-escaping="yes" />
								<xsl:value-of select="string('&lt;/fieldset&gt;')" disable-output-escaping="yes" />
								<xsl:value-of select="string('&lt;/td&gt;')" disable-output-escaping="yes" />
								<xsl:value-of select="string('&lt;/tr&gt;')" disable-output-escaping="yes" />
							</xsl:otherwise>
						</xsl:choose>
					</xsl:if>
				</xsl:for-each>
				
				<!-- Button / Buttons -->
				<xsl:choose>
					<xsl:when test="data/buttons">
						<tr>
							<td>
								<xsl:attribute name="colspan"><xsl:value-of select="$columns"/></xsl:attribute>
								<xsl:for-each select="data/button_cell_attrs/*">
									<xsl:attribute name="{name()}">
										<xsl:value-of select="."/>
									</xsl:attribute>
								</xsl:for-each>
								<xsl:for-each select="data/buttons/*">
									<xsl:if test="string-length(./value) > 0">
										<input type="submit">
											<xsl:attribute name="name"><xsl:value-of select="./name"/></xsl:attribute>
											<xsl:attribute name="value"><xsl:value-of select="./value"/></xsl:attribute>
										</input>
									</xsl:if>
								</xsl:for-each>
							</td>
						</tr>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="data/button">
							<tr>
								<td>
									<xsl:attribute name="colspan"><xsl:value-of select="$columns"/></xsl:attribute>
									<xsl:for-each select="data/button_cell_attrs/*">
										<xsl:attribute name="{name()}">
											<xsl:value-of select="."/>
										</xsl:attribute>
									</xsl:for-each>
									<xsl:if test="string-length(data/button) > 0">
										<input type="submit">
											<xsl:attribute name="value"><xsl:value-of select="data/button"/></xsl:attribute>
										</input>
									</xsl:if>
								</td>
							</tr>
						</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
			</table>
        </fieldset>
    </form>
</xsl:template>

</xsl:stylesheet>