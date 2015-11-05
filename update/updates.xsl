<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="html" />
	<xsl:template match="/">
		<xsl:text disable-output-escaping="yes"><![CDATA[<!DOCTYPE html>]]></xsl:text>
		<html lang="en">
			<head>
				<meta charset="utf-8" />
				<title><xsl:value-of select="/updates/update/name" /> - Available downloads</title>

				<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" />
				<style type="text/css">
					.jumbotron {
						padding: .25em 0;
						border-bottom: 1px solid #333;
					}

					.table .label {
						line-height: inherit;
					}

					.btn > .glyphicon {
						margin-left: .66em;
					}
				</style>
			</head>
			<body>
				<div class="jumbotron">
					<div class="container">
						<h1>
							<xsl:value-of select="/updates/update/name" />
						</h1>
						<p>
							Available downloads
						</p>
					</div>
				</div>
				<div class="container table-responsive">
					<table class="table table-striped table-hover table-condensed">
						<colgroup>
							<col />
							<col span="3" width="12%" />
							<col span="2" width="15%" />
						</colgroup>
						<thead>
							<tr>
								<th class="text-right">
									Version
								</th>
								<th>
									Stability
								</th>
								<th>
									Joomla version
								</th>
								<th>
									PHP version
								</th>
								<th>
									Release notes
								</th>
								<th>
									Downloads
								</th>
							</tr>
						</thead>

						<tbody>
						<xsl:for-each select="updates/update">

							<xsl:variable name="firstRow">
								<xsl:if test="position() = 1">
									<xsl:text>success</xsl:text>
								</xsl:if>
							</xsl:variable>

							<!-- @link https://docs.joomla.org/Deploying_an_Update_Server -->
							<xsl:variable name="stabilityLabel">
								<xsl:choose>
									<xsl:when test="contains(tags/tag, 'stable')">
										<xsl:text>success</xsl:text>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>warning</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:variable>

							<tr class="{$firstRow}">
								<th class="text-right">
									<xsl:value-of select="version" />
								</th>
								<td>
									<span class="label label-{$stabilityLabel}">
										<xsl:value-of select="tags/tag" />
									</span>
								</td>
								<td>
									<span class="badge">
										<xsl:value-of select="targetplatform/@version" />
									</span>
								</td>
								<td>
									<xsl:if test="php_minimum">
										<span class="badge">
											<xsl:value-of select="php_minimum" />
										</span>
									</xsl:if>
								</td>
								<td>
									<a class="btn btn-xs btn-info" href="{infourl}" target="info" title="Open release notes">
										Info
										<span class="glyphicon glyphicon-info-sign"></span>
									</a>
								</td>
								<td>
									<div class="btn-group" role="group">
									<xsl:for-each select="downloads/downloadurl">
										<a class="btn btn-xs btn-primary" href="{.}" target="download" title="Download in {@format} format">
											<xsl:value-of select="@format" />
											<span class="glyphicon glyphicon-download"></span>
										</a>
									</xsl:for-each>
									</div>
								</td>
							</tr>
						</xsl:for-each>
						</tbody>
					</table>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
