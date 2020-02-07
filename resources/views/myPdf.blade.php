<!DOCTYPE html>

<html>
	<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    			
		<style>
			p {
			    color: #000!important;
			    font-weight: 400;
			    display: block;
				font-family: "Lucida Sans Unicode";
			}

		a:link, a:visited, a:hover, a:active {
		  background-color: #4a4a4a;
		  border-radius: 6px;
		  color: white;
		  width: 50px;
		  padding: 7px 17px;
		  text-align: center;
		  text-decoration: none;
		  display: inline-block;
		}			

		li.i {
		  font-style: italic;
		}	

		ul {
		  margin: 0;
		  margin-top: 20px;
		  margin-bottom: 23px;
		}
		ul.dashed {
		  list-style-type: none;
		}
		ul.dashed > li {
		  text-indent: -5px;
		}
		ul.dashed > li:before {
		  content: "-";
		  text-indent: -5px;
		}			
						
		</style>		
	</head>
<body>

  <table width="100%" style="width:80%" border="0">
      <tr>
        <td><p>No Tiket Pengaduan Anda</p></td>
        <td>:</td>
        <td><a href="#"><strong>{{ $ticket }}</strong></a></td>

      </tr>
      <tr>
        <td><p>No Antrian Pengaduan Anda</p></td>
        <td>:</td>
        <td><a href="#"><strong>{{ $antrian }}</strong></a></td>
      </tr>
   </table>
   		<ul class="dashed">
			<li class="i">
				Silahkan simpan nomor tiket Anda untuk melakukan pengecekan status atau sanggahan terhadap informasi yang kami berikan.
			</li>
		<li class="i">
			Rekap pengaduan Anda telah Kami kirimkan juga ke email Anda.
		</li>
	</ul>
	<p class="important">
		Kami akan memproses aduan anda pada jam kerja : 
		<br />
		Senin - Jum'at | 08:00 - 15:30
	</p>	
</body>
</html>
