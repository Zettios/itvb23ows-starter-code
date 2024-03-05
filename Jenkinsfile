pipeline {
    agent any
    stages {
	    stage('Setup') {
            steps {
                script {
                    buildDockerCompose = "docker-compose -f docker-compose.yml build"
                    dockerComposeUp = "docker-compose -f docker-compose.yml up -d"

                    sh "${buildDockerCompose}"
                    sh "${dockerComposeUp}"
                }
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
                    docker.image('itvb23ows-starter-code-app:latest').inside {
                        sh 'php --version'
                        sh 'vendor/bin/phpunit src/.'
                    }
                }
            }
        }

    }
}
