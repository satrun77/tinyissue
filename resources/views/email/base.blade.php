
<table cellpadding="0" cellspacing="0" width="100%"
       style="border-collapse:collapse;background-color:#f5f5f5;border-collapse:collapse" bgcolor="#f5f5f5">
    <tbody>
    <tr>
        <td style="padding:0px;border-collapse:collapse;padding:10px 20px">
            @yield('header', '')
        </td>
    </tr>
    <tr>
        <td style="padding:0px;border-collapse:collapse;padding:0 20px">
            <table cellspacing="0" cellpadding="0" border="0" width="100%"
                   style="border-collapse:collapse;border-spacing:0;border-collapse:separate">
                <tr>
                    <td style="padding:0px;border-collapse:collapse;color:#ffffff;padding:0 15px 0 16px;height:15px;background-color:#ffffff;border-left:1px solid #cccccc;border-top:1px solid #cccccc;border-right:1px solid #cccccc;border-bottom:0;border-top-right-radius:5px;border-top-left-radius:5px;height:10px;line-height:10px;padding:0 15px 0 16px"
                        height="10" bgcolor="#ffffff">&nbsp;</td>
                </tr>
                @yield('body')
                <tr>
                    <td style="padding:0px;border-collapse:collapse;color:#ffffff;padding:0 15px 0 16px;height:5px;line-height:5px;background-color:#ffffff;border-top:0;border-left:1px solid #cccccc;border-bottom:1px solid #cccccc;border-right:1px solid #cccccc;border-bottom-right-radius:5px;border-bottom-left-radius:5px"
                        height="5" bgcolor="#ffffff">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding:0px;border-collapse:collapse;padding:12px 20px">
            @yield('footer', '')
        </td>
    </tr>
    </tbody>
</table>
