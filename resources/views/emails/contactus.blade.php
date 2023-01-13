<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="format-detection" content="telephone=no" />
    <!-- disable auto telephone linking in iOS -->
    <!-- <title>Respmail is a response HTML email designed to work on all major email platforms and smartphones</title> -->
  </head>
  <body bgcolor="#E1E1E1" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    <center style="background-color:#E1E1E1;">
      <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" style="table-layout: fixed;max-width:100% !important;width: 100% !important;min-width: 100% !important;">
        <tr>
          <td align="center" valign="top" id="bodyCell">
            <!-- EMAIL HEADER // -->
            <table bgcolor="#FFFFFF"  border="0" cellpadding="0" cellspacing="0" width="500" id="emailBody">
              <!-- MODULE ROW // -->
              <tr>
                <td align="center" valign="top">
                  <!-- CENTERING TABLE // -->
                  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="color:#FFFFFF;" bgcolor="#3498db">
                    <tr>
                      <td align="center" valign="top">
                        <!-- FLEXIBLE CONTAINER // -->
                        <table border="0" cellpadding="0" cellspacing="0" width="500" class="flexibleContainer">
                          <tr>
                            <td align="center" valign="top" width="500" class="flexibleContainerCell">
                              <!-- CONTENT TABLE // -->
                              <table border="0" cellpadding="30" cellspacing="0" width="100%">
                                <tr>
                                  <td align="center" valign="top" class="textContent" style="padding: 10px;">
                                   <a style="color: #fff;text-decoration: none; font-size: 23px; font-weight: bold;" href="{{url('')}}">Afrobizfind </a>
                                  </td>
                                </tr>
                              </table>
                              <!-- // CONTENT TABLE -->
                            </td>
                          </tr>
                        </table>
                        <!-- // FLEXIBLE CONTAINER -->
                      </td>
                    </tr>
                  </table>
                  <!-- // CENTERING TABLE -->
                </td>
              </tr>
              <!-- // MODULE ROW --> 
              <!-- MODULE ROW // -->
              <tr>
                <td align="center" valign="top">
                  <!-- CENTERING TABLE // -->
                  <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                      <tr>
                        <td align="center" valign="top">
                          <!-- FLEXIBLE CONTAINER // -->
                          <table border="0" cellpadding="0" cellspacing="0" width="500" class="flexibleContainer">
                            <tbody>
                              <tr>
                                <td align="center" valign="top" width="500" class="flexibleContainerCell">
                                  <table border="0" cellpadding="30" cellspacing="0" width="100%">
                                    <tbody>
                                      <tr>
                                        <td align="center" valign="top">
                                          <!-- CONTENT TABLE // -->
                                          <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tbody>
                                              <tr>
                                                <td valign="top" class="textContent">
                                                  <div style="text-align:center;font-family:Helvetica,Arial,sans-serif;font-size:20px;margin-bottom:0;margin-top:3px;color:#5F5F5F;line-height:135%;">Contact us</div>
                                                </td>
                                              </tr>
                                            </tbody>
                                          </table>
                                          <!-- // CONTENT TABLE -->
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                          <!-- // FLEXIBLE CONTAINER -->
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <!-- // CENTERING TABLE -->
                </td>
              </tr>
              <tr>
                <td align="center" valign="top">
                  <!-- CENTERING TABLE // -->
                  <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#fff">
                    <tr>
                      <td align="center" valign="top">
                        <!-- FLEXIBLE CONTAINER // -->
                        <table border="0" cellpadding="0" cellspacing="0" width="500" class="flexibleContainer">
                          <tr>
                            <td align="center" valign="top" width="500" class="flexibleContainerCell">
                              <table border="0" cellpadding="30" cellspacing="0" width="100%">
                                <tr>
                                  <td align="center" valign="top">
                                    <!-- CONTENT TABLE // -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                      <tr>
                                        <td valign="top" class="textContent">
                                          <!--
                                            The "mc:edit" is a feature for MailChimp which allows
                                            you to edit certain row. It makes it easy for you to quickly edit row sections.
                                            http://kb.mailchimp.com/templates/code/create-editable-content-areas-with-mailchimps-template-language
                                            -->
                                          <div mc:edit="body" style="text-align:left;font-family:Helvetica,Arial,sans-serif;font-size:15px;margin-bottom:0;color:#5F5F5F;line-height:135%;">


                                          		<div style="display: block; width: 100%">
                                          			<div  style="    display: inline-block;"><h4 style="color:#5F5F5F;    margin: 0;">Name:</h4></div>
                                          			<div style="    display: inline-block;">{{ $contact['name'] }} </div>

                                          		</div>
                                          		<div style="display: block; width: 100%">
                                          			<div  style="    display: inline-block;"><h4 style="color:#5F5F5F;margin: 0;">E-Mail:</h4></div>
                                          			<div style="    display: inline-block;">{{ $contact['email'] }}</div>

                                          		</div>
                                          		<div style="display: block; width: 100%">
                                          			<div  style="    display: inline-block;"><h4 style="color:#5F5F5F;margin: 0;">Message:</h4></div>
                                          			<div style="    display: inline-block;">{{ $contact['message'] }}</div>

                                          		</div>

                                          </div>
                                        </td>
                                      </tr>
                                    </table>
                                    <!-- // CONTENT TABLE -->
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                        <!-- // FLEXIBLE CONTAINER -->
                      </td>
                    </tr>
                  </table>
                  <!-- // CENTERING TABLE -->
                </td>
              </tr>
              <!-- // MODULE ROW -->
              <!-- MODULE DIVIDER // -->
              <tr>
                <td align="center" valign="top">
                  <!-- CENTERING TABLE // -->
                  <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td align="center" valign="top">
                        <!-- FLEXIBLE CONTAINER // -->
                        <table border="0" cellpadding="0" cellspacing="0" width="500" class="flexibleContainer">
                          <tr>
                            <td align="center" valign="top" width="500" class="flexibleContainerCell">
                              <table class="flexibleContainerCellDivider" border="0" cellpadding="30" cellspacing="0" width="100%">
                                <tr>
                                  <td align="center" valign="top" style="padding-top:0px;padding-bottom:0px;">
                                    <!-- CONTENT TABLE // -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                      <tr>
                                        <td align="center" valign="top" style="border-top:1px solid #C8C8C8;"></td>
                                      </tr>
                                    </table>
                                    <!-- // CONTENT TABLE -->
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                        <!-- // FLEXIBLE CONTAINER -->
                      </td>
                    </tr>
                  </table>
                  <!-- // CENTERING TABLE -->
                </td>
              </tr>
              <!-- // END -->
            </table>
           
          </td>
        </tr>
      </table>
    </center>
  </body>
</html>