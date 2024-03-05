pipeline {
    agent any
    stages {
	    stage('Setup') {
            steps {
                echo 'Do some setup'
            }
        }
        stage('Execute SonarQube scan') {
            agent {
                label '!windows'
            }
            steps {
                echo 'Executing SonarQube scan'
                script {
                    scannerHome = tool 'SonarQube Scanner'
                }
                withSonarQubeEnv('SonarQube_Hive') {
                    sh "${scannerHome}/bin/sonar-scanner -Dsonar.projectKey=sqp_95ec0aa97b17a116a718b1ce1f5240e2dc006d9e"
                }
            }
        }
        stage('Execute PHPUnit Tests') {
            steps {
                script {
                    docker.image('composer:lts').inside {
                        sh 'composer install'
                        sh 'vendor/bin/phpunit src/.'
                    }
                }
            }
        }

    }
}