# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.network "private_network", ip: "192.168.62.10"
  
  config.vm.provider "virtualbox" do |vb|
     vb.memory = "2048"

     # A fix for speed issues with DNS resolution:
     # http://serverfault.com/questions/453185/vagrant-virtualbox-dns-10-0-2-3-not-working?rq=1
     vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
  end

  # This provisioner runs on the first `vagrant up`.
  config.vm.provision "install", type: "shell", inline: <<-SHELL
    # Add Docker apt repository
    sudo apt-key adv --keyserver hkp://p80.pool.sks-keyservers.net:80 --recv-keys 58118E89F3A912897C070ADBF76221572C52609D
    sudo sh -c 'echo deb https://apt.dockerproject.org/repo ubuntu-trusty main > /etc/apt/sources.list.d/docker.list'
    sudo apt-get update -y
    # Add NTP so that the LDAP queries don't unexpectedly fail.
    sudo apt-get install ntp -y
    # Uninstall old lxc-docker
    apt-get purge lxc-docker
    apt-cache policy docker-engine
    # Install docker and dependencies
    sudo apt-get install -y linux-image-extra-$(uname -r)
    sudo apt-get install -y docker-engine
    # Add user vagrant to docker group
    sudo groupadd docker
    sudo usermod -aG docker vagrant
    # Install Docker Compose
    curl -L https://github.com/docker/compose/releases/download/1.9.0/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
  SHELL

  # This provisioner runs on every `vagrant reload' (as well as the first
  # `vagrant up`), reinstalling from local directories
  config.vm.provision "recompose", type: "shell",
     run: "always", inline: <<-SHELL

    # Run docker-compose (which will update preloaded images, and
    # pulls any images not preloaded)
    cd /vagrant
    
    # Set necessary environment variables for shell access.
    export COMPOSER_CACHE_DIR=/tmp
    export DOCKER_UIDGID="$(id -u):$(id -g)"
    
    # Set up necessary env. vars to automatically be present each time.
    cat << 'EOF' >> /home/vagrant/.bashrc
export COMPOSER_CACHE_DIR=/tmp
export DOCKER_UIDGID="$(id -u):$(id -g)"
EOF

  SHELL

end
