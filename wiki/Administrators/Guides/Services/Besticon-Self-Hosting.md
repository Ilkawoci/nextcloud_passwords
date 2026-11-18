[Besticon](https://github.com/mat/besticon) is a favicon service written in Go which can be used to fetch icons for websites.
The passwords app offers an integration to connect with a server hosting the service.

## Running your own instance of Besticon

### With Heroku
1. [Create](https://signup.heroku.com/) or [log into](https://id.heroku.com/login) your Heroku account.
2. [Click this link](https://dashboard.heroku.com/new?button-url=https%3A%2F%2Fgithub.com%2Fmat%2Fbesticon&template=https%3A%2F%2Fgithub.com%2Fmat%2Fbesticon).
3. Choose an app name and click "Deploy app".
4. Click on "View" to view the app and save the url.
5. Now [configure the app](#configure-the-app).

### With Docker
Please note that we expect you to have [docker](https://get.docker.com/) installed.
You need to run these commands on your server an you need to know the domain of your server.

1. Pull the image with `docker pull matthiasluedtke/iconserver`.
2. Start the container with `docker run -d --restart=on-failure -p 8080:8080 --name=besticon matthiasluedtke/iconserver`.
   - If you do not want to user port 8080, replace `-p 8080:8080` with `-p <yourport>:8080`.
3. Open the frontend on port 8080.
   - If the frontend does not work, but the container is running (see `docker ps`), check the logs and your firewall.
   - We do not recommend making the container accessible to the web.
4. Now [configure the app](#configure-the-app).

### With Docker Compose
Please note that we expect you to have [docker](https://get.docker.com/) and [docker-compose](https://docs.docker.com/compose/install/) installed.

1. The docker-compose.yml file:
    ```yaml
    version: '2'
    
    services:
      nextcloud-besticon:
        image: matthiasluedtke/iconserver
        container_name: "nextcloud-besticon"
    # If you integrate this service into your existing docker-compose setup,
    # then you don't need to expose the port and can just contact the container directly 
    # with http://nextcloud-besticon:8080/icon
        ports:
          - 8080:8080
    #    environment:
    #      PORT: Your Port Here
    ```
2. Run `docker-compose up -d`
3. Open the frontend on port 8080,
   - If the frontend does not work, but the container is running (see `docker ps`), check the logs and your firewall.
   - We do not recommend making the container accessible to the web.
4. Now [configure the app](#configure-the-app).

### Build it yourself (Suggested for Raspberry PI / ARM)
These commands will compile the application from source.
You can also use our [precompiled arm binary](https://raw.githubusercontent.com/wiki/marius-wieschollek/passwords/_files/iconserver) and go directly to the [standalone installation](#standalone-with-autostart).

1. Install GO: `sudo apt install golang git`
2. Download and compile the app with `go get -u github.com/mat/besticon/...`
3. Open the binary folder: `cd ~/go/bin/`
3. Now continue with step 3 from the [standalone installation](#standalone-with-autostart).

### Standalone with autostart
You need command line access and root privileges for this guide.
(NextCloudPI users: Enable SSH in the configuration interface and then log in with `ssh pi@yournextcloudpi`)
You need to run these commands on your server an you need to know the domain of your server.

1. Download the [binary](https://github.com/mat/besticon/releases) for your server.
2. Unzip it with `unzip iconserver_linux_amd64.zip`
   - If you unzipped it into a subfolder, go there
3. Go root: `sudo su`
4. Move it to the bin folder with `mv iconserver /usr/local/bin/`
5. Create the systemd service file with `sudo nano /lib/systemd/system/iconserver.service` with the following content:
    ```
    [Unit]
    Description=Besticon Favicon Server
    After=multi-user.target
    
    [Service]
    Environment=PORT=8080
    ExecStart=/usr/local/bin/iconserver  
    
    [Install]
    WantedBy=multi-user.target
    ```
6. Rescan systemd services with `sudo systemctl daemon-reload`
7. Enable the new iconserver service with `sudo systemctl enable iconserver.service`
8. Start the service with `sudo systemctl start iconserver` or reboot with `sudo reboot`
9. Open the frontend on port 8080,
   - If the frontend does not work, check the logs and your firewall.
   - We do not recommend making the container accessible to the web.
10. Now [configure the app](#configure-the-app).

### Standalone - Just run it
You need command line access for this guide.
You need to run these commands on your server an you need to know the domain of your server.

1. Download the [binary](https://github.com/mat/besticon/releases) for your server.
2. Unzip it `unzip iconserver_linux_amd64.zip`
   - If you unzipped it into a subfolder, go there
3. Run `PORT=8080 ./iconserver &` and then `disown`
4. Open the frontend on port 8080,
   - If the frontend does not work, check the logs and your firewall.
   - We do not recommend making the container accessible to the web.
5. Now [configure the app](#configure-the-app).

## Configure the app
1. Open the admin settings of the passwords app ("Settings" > "Passwords") in your Nextcloud.
2. Set "Favicon Service" to "Besticon (recommended)".
3. Set the "Favicon Service Api" to `http://your_domain_or_ip:8080/icon`
   - If you use https or a custom port update the domain accordingly.
   - If you use the ip or a local domain to host besticon, set `allow_local_remote_servers` to `true` in the config.php
4. Reload the page
5. Clear the cache "Favicon"
6. To test the settings:
   1. Press F12 in your browser
   2. Open the "Network" tab
   3. Check the "Disable Cache" option
   4. Open the passwords app and check if favicons appear. You can filter for requests with `passwords/api/1.0/service/favicon`
