<table @mailattrs(table)>
    <tr>
        <td @mailattrs(cell| width=32)>
            <img src="{{ $changeByImage }}" height="32" width="32"  @mailattrs(image)>
        </td>
        <td @mailattrs(cell| valign=middle)>
            <p>{!! $changeByHeading !!}</p>
        </td>
    </tr>
</table>
