<!DOCTYPE html>
<html>
<head>
	<title>{{ config('constants.SITE_TITLE') }} | Email</title>
</head>
<style>

</style>
<body>
	<table class="jetpakistan-email-template-main" style="margin: 0 auto;background: #fff;border-collapse: collapse;border-spacing: 0;margin: 0 auto;padding-bottom: 0;padding-left: 0;padding-right: 0;padding-top: 0;text-align: inherit;vertical-align: top;width: 700px;">
		<tbody>
			<tr style="padding-bottom:0;padding-left:0;padding-right:0;padding-top:0;text-align:left;vertical-align:top">
				<td style="margin:0;border-collapse:collapse!important;color:#000;font-family:Roboto,sans-serif;font-size:14px;font-weight:400;line-height:1.3;margin:0;padding-bottom:0;padding-left:0;padding-right:0;padding-top:0;text-align:left;vertical-align:top;word-wrap:break-word">
					
					
					
					<table style="border-collapse:collapse;border-spacing:0;padding-bottom:0;padding-left:0;padding-right:0;padding-top:0;text-align:left;vertical-align:top;width:100%">
						<tbody>
							<tr style="padding-bottom:0;padding-left:0;padding-right:0;padding-top:0;text-align:left;vertical-align:top">
								<td height="20" style="Margin:0;border-collapse:collapse!important;color:#000;font-family:Roboto,sans-serif;font-size:20px;font-weight:400;line-height:20px;margin:0;padding-bottom:0;padding-left:0;padding-right:0;padding-top:0;text-align:left;vertical-align:top;word-wrap:break-word">&nbsp;</td>
							</tr>
						</tbody>
					</table>
					<table style="border-collapse:collapse;border-spacing:0;display:table;padding:0;padding-bottom:0;padding-left:0;padding-right:0;padding-top:0;text-align:left;vertical-align:top;width:100%">
						<tbody>
							<tr style="padding-bottom:0;padding-left:0;padding-right:0;padding-top:0;text-align:left;vertical-align:top">
								<th style="Margin:0 auto;border-collapse:collapse!important;color:#000;font-family:Roboto,sans-serif;font-size:14px;font-weight:400;line-height:1.3;margin:0 auto;padding-bottom:5px;padding-left:12px!important;padding-right:12px!important;padding-top:0;text-align:left;vertical-align:top;width:668px;word-wrap:break-word">
									<table style="border-collapse:collapse;border-spacing:0;padding-bottom:0;padding-left:0!important;padding-right:0!important;padding-top:0;text-align:left;vertical-align:top;width:100%">
										<tbody style="padding-left:0!important;padding-right:0!important">
											<tr style="padding-bottom:0;padding-left:0!important;padding-right:0!important;padding-top:0;text-align:left;vertical-align:top">
												<th style="Margin:0;border-collapse:collapse!important;color:#000;font-family:Roboto,sans-serif;font-size:14px;font-weight:400;line-height:1.3;margin:0;padding-bottom:0;padding-left:0!important;padding-right:0!important;padding-top:0;text-align:left;vertical-align:top;word-wrap:break-word">
													<h6 style="Margin:0;Margin-bottom:10px;color:inherit;font-family:Roboto,sans-serif;font-size:17px;font-weight:500;line-height:32px;margin:0;margin-bottom:10px;padding-bottom:0;padding-left:0!important;padding-right:0!important;padding-top:0;text-align:left;word-wrap:normal">
														Dear {{ $first_name.' '.$last_name }}
													</h6>
													<p style="Margin:0;Margin-bottom:10px;color:#4c4c4c;font-family:Roboto,sans-serif;font-size:20px;font-weight:700;line-height:32px;margin:0;margin-bottom:10px;padding-bottom:0;padding-left:0!important;padding-right:0!important;padding-top:0;text-align:center">
														OTP Code: {{ $otp_code }}
													</p>
													<p style="Margin:0;Margin-bottom:10px;color:#4c4c4c;font-family:Roboto,sans-serif;font-size:14px;font-weight:400;line-height:32px;margin:0;margin-bottom:10px;padding-bottom:0;padding-left:0!important;padding-right:0!important;padding-top:0;text-align:left;text-align:center">
														Please Use above 6 digit otp code for login
													</p>
												</th>
											</tr>
										</tbody>
									</table>
								</th>
							</tr>
						</tbody>
					</table>
					
					<table style="border-collapse:collapse;border-spacing:0;padding-bottom:0;padding-left:0;padding-right:0;padding-top:0;text-align:left;vertical-align:top;width:100%">
						<tbody>
							<tr style="padding-bottom:0;padding-left:0;padding-right:0;padding-top:0;text-align:left;vertical-align:top">
								<td height="20" style="Margin:0;border-collapse:collapse!important;color:#000;font-family:Roboto,sans-serif;font-size:20px;font-weight:400;line-height:20px;margin:0;padding-bottom:0;padding-left:0;padding-right:0;padding-top:0;text-align:left;vertical-align:top;word-wrap:break-word">&nbsp;</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
</body>
</html>