<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Project name</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="index.php">Home</a></li>
                <?php
                if(logged_in()){
                    echo "<li class='active'><a href='admin.php'>Admin</a></li>";
                }
                ?>
                <?php
                if(logged_in()){
                    
                }else{
                    echo "<li class='active'><a href='login.php'>Login</a></li>";
                }
                
                ?>
                
                <?php
                if(logged_in()){
                    echo "<li class='active'><a href='logout.php'>Logout</a></li>";
                }
                
                ?>
                                <?php
                if(logged_in()){
                    
                }else{
                    echo "<li class='active'><a href='register.php'>Register</a></li>";
                }
                
                ?>
                
            </ul>
        </div>
        <!--/.nav-collapse -->
    </div>
</nav>