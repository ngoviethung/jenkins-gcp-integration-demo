pipeline {
    agent any
    environment {
        PROJECT_ID = 'du01-android---wedding'
        CLUSTER_NAME = 'du01-test-deploy'
        LOCATION = 'us-central1-a'
        CREDENTIALS_ID = 'du01-test-deploy'
        DOCKER_HUB_USERNAME= 'hungnv93'
        DOCKER_HUB_REPOSITORY_NAME= 'hello'
    }
    stages {
        stage("Checkout code") {
            steps {
                checkout scm
            }
        }
        stage("Build image") {
            steps {
                script {
                    myapp = docker.build("${env.DOCKER_HUB_USERNAME}/${env.DOCKER_HUB_REPOSITORY_NAME}:${env.BUILD_ID}")
                }
            }
        }
        stage("Push image") {
            steps {
                script {
                    docker.withRegistry('https://registry.hub.docker.com', 'dockerID') {
                            myapp.push("latest")
                            myapp.push("${env.BUILD_ID}")
                    }
                }
            }
        }        
        stage('Deploy to GKE') {
            steps{
                sh "sed -i 's/hello:latest/hello:${env.BUILD_ID}/g' deployment.yaml"
                step([$class: 'KubernetesEngineBuilder', projectId: env.PROJECT_ID, clusterName: env.CLUSTER_NAME, location: env.LOCATION, manifestPattern: 'deployment.yaml', credentialsId: env.CREDENTIALS_ID, verifyDeployments: true])
            }
        }
    }    
}