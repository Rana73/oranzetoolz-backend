<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Password Reset Url</title>
    <style>
        .logo-area{
          margin-bottom: 100px;  
        }
    	.button{
    		background: #202c45;
    		color: white;
    		border-radius: 5px;
    		width: auto;
    		padding: 10px 20px;
    		text-decoration: none;
            color: white !important;
    	}
        .footer-text{
            margin-top: 30px;
            text-align: justify;
        }
    </style>
</head>
<body>

<div class="template" style="padding: 50px;">
		<div class="template-text">
            <div style="display: flex;justify-content:space-between" class="logo-area">
                <div><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5d/GNOME_Todo_icon_2019.svg/1200px-GNOME_Todo_icon_2019.svg.png" style="width:auto;height:90px;float:left;margin-right:20px;margin-left:90px;"></div>
                <div><h1>Task Management System</h1></div>
            </div>

			<h2>Dear {{ $user['name']  }},</h2>
		    <h4>To reset your password click the Reset Password button.</h4><br>
		    <a class="button" href="{{$user['url']}}">
		    	Reset Password
		    </a>

		    <p class="footer-text"> <span style="color:red">This link will expire in 5 minutes.</span> If you did not make this request, please disregard this email. For help, contact us through our Help center.</p>
		</div>
	</div>
</body>
</html>
