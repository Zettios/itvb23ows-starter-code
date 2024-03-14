pipeline {
    agent any
    stages {
	    stage('Setup') {
	        agent { docker }
            steps {
                sh "docker build -t itvb23ows-starter-code-app"
            }
        }
        stage('Quick start example test') {
            agent { docker { image 'php:8.3.3-alpine3.19' } }
            steps {
                sh 'php --version'
            }
        }
        stage('Execute SonarQube scan') {
            agent { label '!windows' }
            steps {
                echo 'Executing SonarQube scan'
                script {
                    scannerHome = tool 'SonarQube Scanner'
                }
                withSonarQubeEnv('SonarQube_Hive') {
                    sh "${scannerHome}/bin/sonar-scanner -Dsonar.projectKey=sqp_0372b2f63613590516a6fd678db64083e4a1f43a"
                }
            }
        }
        stage('Execute PHPUnit Tests') {
            agent { docker }
            steps {
                sh "docker run --rm itvb23ows-starter-code-app"
                script {
                    docker.image('itvb23ows-starter-code-app').inside {
                        sh 'php --version'
                        sh './vendor/bin/phpunit src/.'
                    }
                }
            }
        }
    }
    post {
        always {
            echo 'Done'
        }
        success {
            echo "Build successful"
        }
        failure {
            echo "Build failed, see console for the details"
        }
    }
}