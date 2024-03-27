pipeline {
    agent any
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
	    stage('Setup') {
	        steps {
                sh 'composer install'
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
            agent { docker { image 'php:8.2-apache' } }
            steps {
                script {
                    // Run PHPUnit tests
                    sh "./vendor/bin/phpunit"
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