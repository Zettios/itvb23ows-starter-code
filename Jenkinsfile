pipeline {
    agent any

    stages {
	    stage('Setup') {
            steps {
                echo 'Setup fase'
            }
        }
        stage('Quick start example test') {
            agent { docker { image 'php:8.3.3-alpine3.19' } }
            steps {
                sh 'php --version'
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
                    sh "${scannerHome}/bin/sonar-scanner -Dsonar.projectKey=sqp_cfa07620b9d512842a7ae416afb2ab4b4cca6e4b"
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